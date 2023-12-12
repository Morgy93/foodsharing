<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Lib\FoodsharingController;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;
use Foodsharing\Permissions\ContentPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Parsedown;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends FoodsharingController
{
    private const SUB_TO_ID = [
        'presse' => ContentId::PRESS,
        'communitiesGermany' => ContentId::COMMUNITIES_GERMANY,
        'communitiesAustria' => ContentId::COMMUNITIES_AUSTRIA,
        'communitiesSwitzerland' => ContentId::COMMUNITIES_SWITZERLAND,
        'forderungen' => ContentId::DEMANDS,
        'contact' => ContentId::CONTACT,
        'academy' => ContentId::ACADEMY,
        'festival' => ContentId::FESTIVAL,
        'international' => ContentId::INTERNATIONAL,
        'transparency' => ContentId::TRANSPARENCY,
        'leeretonne' => ContentId::CAMPAIGN_LEERE_TONNE,
        'newsFromIT' => ContentId::NEWS_FROM_IT,
        'foodSharePointRescue' => ContentId::RESCUE_FOOD_SHARE_POINT,
        'impressum' => ContentId::IMPRESSUM,
        'about' => ContentId::ABOUT,
        'fuer_unternehmen' => ContentId::FOR_COMPANIES,
        'fsstaedte' => ContentId::FOODSHARING_STAEDTE,
        'workshops' => ContentId::WORKSHOPS,
        'security' => ContentId::SECURITY_PAGE,
    ];

    public function __construct(
        private readonly ContentView $view,
        private readonly ContentGateway $contentGateway,
        private readonly IdentificationHelper $identificationHelper,
        private readonly DataHelper $dataHelper,
        private readonly ContentPermissions $contentPermissions,
    ) {
        parent::__construct();
    }

    #[Route(path: '/content/{name}', name: 'content_show', requirements: ['name' => '[A-Za-z_]+'])]
    public function show(Request $request, string $name): Response
    {
        return $this->viewContent($request, $name);
    }

    #[Route(path: '/content', name: 'content_index')]
    public function index(Request $request): Response
    {
        if ($sub = $request->query->get('sub')) {
            return $this->viewContent($request, $sub);
        }

        if (!$this->contentPermissions->mayEditContent()) {
            return $this->redirect('/');
        }

        if ($this->identificationHelper->getAction('neu')) {
            $this->handle_add();

            $this->pageHelper->addBread($this->translator->trans('content.bread'), '/content');
            $this->pageHelper->addBread($this->translator->trans('content.new'));

            $this->pageHelper->addContent($this->content_form());

            $this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
                ['href' => '/content', 'name' => $this->translator->trans('bread.backToOverview')]
            ]), $this->translator->trans('content.actions')), CNT_RIGHT);
        } elseif ($id = $this->identificationHelper->getActionId('delete')) {
            if ($this->contentGateway->delete($id)) {
                $this->flashMessageHelper->success($this->translator->trans('content.delete_success'));
                $this->routeHelper->goPageAndExit();
            }
        } elseif ($id = $this->identificationHelper->getActionId('edit')) {
            if (!$this->contentPermissions->mayEditContentId((int)$_GET['id'])) {
                return $this->redirect('/content');
            }
            $this->handle_edit();

            $this->pageHelper->addBread($this->translator->trans('content.bread'), '/content');
            $this->pageHelper->addBread($this->translator->trans('content.edit'));

            $data = $this->contentGateway->getDetail($id);
            $this->dataHelper->setEditData($data);

            $this->pageHelper->addContent($this->content_form());

            $this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
                ['href' => '/content', 'name' => $this->translator->trans('bread.backToOverview')]
            ]), $this->translator->trans('content.actions')), CNT_RIGHT);
        } elseif ($id = $this->identificationHelper->getActionId('view')) {
            $this->addContent($id);
        } elseif (isset($_GET['id'])) {
            return $this->redirect('/content?a=edit&id=' . (int)$_GET['id']);
        } else {
            $this->pageHelper->addBread($this->translator->trans('content.public'), '/content');

            $this->pageHelper->addContent($this->view->vueComponent('content-list', 'ContentList', [
                'mayEditContent' => $this->contentPermissions->mayEditContent(),
                'mayCreateContent' => $this->contentPermissions->mayCreateContent(),
            ]));
        }

        return $this->renderGlobal();
    }

    public function partner(): Response
    {
        // select the partners page for the country and use german as fallback
        $host = $_SERVER['HTTP_HOST'] ?? BASE_URL;
        $contentId = ContentId::PARTNER_PAGE_10;
        if (str_contains($host, 'foodsharing.at')) {
            $contentId = ContentId::PARTNER_PAGE_AU_79;
        }

        $this->addContent($contentId);

        return $this->renderGlobal();
    }

    public function ratgeber(): Response
    {
        return $this->redirect('https://wiki.foodsharing.de/Hygiene-Ratgeber_f%C3%BCr_Lebensmittel', 301);
    }

    public function joininfo(): Response
    {
        $this->pageHelper->addBread($this->translator->trans('startpage.join'));
        $this->pageHelper->addTitle($this->translator->trans('startpage.join_rules'));
        $this->pageHelper->addContent($this->view->joininfo());

        return $this->renderGlobal();
    }

    public function releaseNotes(): Response
    {
        $releaseIds = ['2023-09', '2022-12', '2022-05', '2022-01', '2021-09', '2021-03', '2020-12', '2020-10',
            '2020-08', '2020-05'];
        $releaseList = array_map(function ($id) {
            return [
                'id' => $id,
                'title' => $this->translator->trans('releases.' . $id),
                'markdown' => $this->parseGitlabLinks($this->getnotes($id)),
                'visible' => false,
            ];
        }, $releaseIds);
        $releaseList[0]['visible'] = true;

        $this->pageHelper->addContent($this->view->vueComponent('vue-release-notes', 'ReleaseNotes', [
            'releaseList' => $releaseList,
        ]));

        return $this->renderGlobal();
    }

    public function changelog(): Response
    {
        $this->pageHelper->addBread($this->translator->trans('content.changelog'));
        $this->pageHelper->addTitle($this->translator->trans('content.changelog'));

        $projectDir = $this->getParameter('kernel.project_dir');
        $markdown = $this->parseGitlabLinks(file_get_contents($projectDir . '/CHANGELOG.md') ?: '');
        $Parsedown = new Parsedown();
        $cl['title'] = $this->translator->trans('content.changelog');
        $cl['body'] = $Parsedown->parse($markdown);
        $this->pageHelper->addContent($this->view->simple($cl));

        return $this->renderGlobal();
    }

    private function content_form(string $titleKey = 'contentmanagement'): string
    {
        $title = $this->translator->trans($titleKey);

        return $this->v_utils->v_form('faq', [
            $this->v_utils->v_field(
                $this->v_utils->v_form_text('name', ['required' => true]) .
                $this->v_utils->v_form_text('title', ['required' => true]),
                $title,
                ['class' => 'ui-padding']
            ),
            $this->v_utils->v_field(
                $this->v_utils->v_form_tinymce('body', [
                    'public_content' => true,
                    'nowrapper' => true,
                ]),
                $this->translator->trans('content.content')
            )
        ], ['submit' => $this->translator->trans('button.save')]);
    }

    private function handle_edit(): void
    {
        global $g_data;
        if ($this->submitted()) {
            $g_data['last_mod'] = date('Y-m-d H:i:s');
            if ($this->contentGateway->update($_GET['id'], $g_data)) {
                $this->flashMessageHelper->success($this->translator->trans('content.edit_success'));
                $this->routeHelper->goAndExit('/content?a=edit&id=' . (int)$_GET['id']);
            } else {
                $this->flashMessageHelper->error($this->translator->trans('error_unexpected'));
            }
        }
    }

    private function handle_add(): void
    {
        global $g_data;
        if ($this->submitted()) {
            $g_data['last_mod'] = date('Y-m-d H:i:s');
            if ($this->contentGateway->create($g_data)) {
                $this->flashMessageHelper->success($this->translator->trans('content.new_success'));
                $this->routeHelper->goPageAndExit();
            } else {
                $this->flashMessageHelper->error($this->translator->trans('error_unexpected'));
            }
        }
    }

    private function submitted(): bool
    {
        return !empty($_POST);
    }

    private function getnotes(string $filename): string
    {
        $projectDir = $this->getParameter('kernel.project_dir');

        return file_get_contents($projectDir . '/release-notes/' . $filename . '.md') ?: '';
    }

    private function parseGitlabLinks($markdown)
    {
        $markdown = preg_replace('/\W@(\S+)/', ' [@\1](https://gitlab.com/\1)', $markdown) ?? $markdown;
        $markdown = preg_replace('/(android)!([0-9]+)/', '[\1!\2](https://gitlab.com/foodsharing-dev/foodsharing-android/merge_requests/\2)', $markdown) ?? $markdown;
        $markdown = preg_replace('/(android)#([0-9]+)/', '[\1#\2](https://gitlab.com/foodsharing-dev/foodsharing-android/issues/\2))', $markdown) ?? $markdown;
        $markdown = preg_replace('/\W!([0-9]+)/', ' [!\1](https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/\1)', $markdown) ?? $markdown;
        $markdown = preg_replace('/\W#([0-9]+)/', ' [#\1](https://gitlab.com/foodsharing-dev/foodsharing/issues/\1)', $markdown) ?? $markdown;

        return $markdown;
    }

    private function addContent(int $contentId): void
    {
        if ($cnt = $this->contentGateway->get($contentId)) {
            $this->pageHelper->addBread($cnt['title']);
            $this->pageHelper->addTitle($cnt['title']);
            $this->pageHelper->addContent($this->view->simple($cnt));
        }
    }

    private function viewContent(Request $request, string $name): Response
    {
        if (key_exists($name, self::SUB_TO_ID)) {
            $this->addContent(self::SUB_TO_ID[$name]);

            return $this->renderGlobal();
        } elseif (is_callable([$this, $name])) {
            return $this->$name($request);
        } else {
            throw new NotFoundHttpException();
        }
    }
}
