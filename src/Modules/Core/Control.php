<?php

namespace Foodsharing\Modules\Core;

use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\FlashMessageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\TranslationHelper;
use ReflectionClass;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class Control
{
    protected bool $isControl = false;
    protected bool $isXhrControl = false;
    protected $view;
    private $sub;

    protected PageHelper $pageHelper;
    protected Mem $mem;
    protected \Foodsharing\Lib\Session $session;
    protected Utils $v_utils;
    private \Twig\Environment $twig;
    private FoodsaverGateway $foodsaverGateway;
    private InfluxMetrics $metrics;
    protected EmailHelper $emailHelper;
    protected FlashMessageHelper $flashMessageHelper;
    protected RouteHelper $routeHelper;
    protected TranslationHelper $translationHelper;
    protected TranslatorInterface $translator;

    public function __construct()
    {
        global $container;
        $this->mem = $container->get(Mem::class);
        $this->session = $container->get(Session::class);
        $this->v_utils = $container->get(Utils::class);
        $this->foodsaverGateway = $container->get(FoodsaverGateway::class);
        $this->metrics = $container->get(InfluxMetrics::class);
        $this->pageHelper = $container->get(PageHelper::class);
        $this->emailHelper = $container->get(EmailHelper::class);
        $this->routeHelper = $container->get(RouteHelper::class);
        $this->flashMessageHelper = $container->get(FlashMessageHelper::class);
        $this->translationHelper = $container->get(TranslationHelper::class);
        $this->translator = $container->get('translator'); // TODO TranslatorInterface is an alias

        $reflection = new ReflectionClass($this);
        $className = $reflection->getShortName();

        $this->sub = false;
        if (isset($_GET['sub'])) {
            $sub = $_GET['sub'];

            if (method_exists($this, $sub)) {
                $this->sub = $sub;
            }
        }

        if (($pos = strpos($className, 'Control')) !== false) {
            $this->isControl = true;
        } elseif (($pos = strpos($className, 'Xhr')) !== false) {
            $this->isXhrControl = true;
        }

        /*if ($this->isControl) {
            $projectDir = $container->get('kernel')->getProjectDir();
            $webpackModules = $projectDir . '/assets/modules.json';
            $manifest = json_decode(file_get_contents($webpackModules), true);
            $moduleName = substr($className, 0, $pos);
            $entry = 'Modules/' . $moduleName;
            if (isset($manifest[$entry])) {
                foreach ($manifest[$entry] as $asset) {
                    if (str_ends_with($asset, '.js')) {
                        $this->pageHelper->addWebpackScript($asset);
                    } elseif (str_ends_with($asset, '.css')) {
						$this->pageHelper->addWebpackStylesheet($asset);
					}
				}
			}
		} */
        $this->metrics->addPageStatData(['controller' => $className]);
    }

    /**
     * @required
     */
    public function setTwig(\Twig\Environment $twig): void
    {
        $this->twig = $twig;
    }

    protected function render($template, $data)
    {
        $global = $this->pageHelper->generateAndGetGlobalViewData();
        $viewData = array_merge($global, $data);

        return $this->twig->render($template, $viewData);
    }

    public function setTemplate($template)
    {
        global $g_template;
        $g_template = $template;
    }

    public function getSub()
    {
        return $this->sub;
    }

    public function getRequest($name)
    {
        if (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        }

        return false;
    }

    public function wallposts($table, $id): string
    {
        $posthtml = '';
        if ($this->session->mayRole()) {
            $posthtml = '
				<div class="tools ui-padding">
				<textarea id="wallpost-text" name="text" placeholder="' . $this->translator->trans('wall.message_placeholder') . '" class="comment textarea"></textarea>
				<div id="attach-preview"></div>
				<div style="display: none;" id="wallpost-attach" /></div>

				<div id="wallpost-submit" align="right">

					<span id="wallpost-loader"></span><span id="wallpost-attach-image"><i class="far fa-image"></i> ' . $this->translator->trans('button.attach_image') . '</span>
					<a href="#" id="wall-submit">' . $this->translator->trans('button.send') . '</a>
					<div style="overflow: hidden; height: 0;">
						<form id="wallpost-attachimage-form" action="/xhrapp.php?app=wallpost&m=attachimage&table=' . $table . '&id=' . $id . '" method="post" enctype="multipart/form-data" target="wallpost-frame">
							<input id="wallpost-attach-trigger" type="file" accept="image/png, image/jpeg" maxlength="100000" size="chars" name="etattach" />
						</form>
					</div>

				</div>
				<div class="clear"></div>
				<div style="visibility: hidden;">
				<iframe name="wallpost-frame" style="height: 1px;" frameborder="0"></iframe>
				</div>
			</div>';
        }

        return '
		<div id="wallposts">
			' . $posthtml . '
			<div class="wall-posts">

			</div>
		</div>';
    }

    public function submitted(): bool
    {
        return !empty($_POST);
    }

    public function isSubmitted($form = false): bool
    {
        if (!empty($_POST)) {
            return $form === false || $_POST['submitted'] == $form;
        }

        return false;
    }

    public function getPostDate($name)
    {
        if ($date = $this->getPostString($name)) {
            $date = explode(' ', $date);
            $date = trim($date[0]);
            if (!empty($date)) {
                $date = explode('-', $date);
                if (count($date) == 3 && (int)$date[0] > 0 && (int)$date[1] > 0 && (int)$date[2] > 0) {
                    return mktime(0, 0, 0, (int)$date[1], (int)$date[2], (int)$date[0]);
                }
            }
        }

        return false;
    }

    public function getPostTime($name)
    {
        if (isset($_POST[$name]['hour'], $_POST[$name]['min'])) {
            return [
                'hour' => (int)$_POST[$name]['hour'],
                'min' => (int)$_POST[$name]['min']
            ];
        }

        return false;
    }

    public function getPostString($name)
    {
        if ($val = $this->getPost($name)) {
            $val = strip_tags($val);
            $val = trim($val);

            if (!empty($val)) {
                return $val;
            }
        }

        return false;
    }

    public function getPostInt($name)
    {
        if ($val = $this->getPost($name)) {
            $val = trim($val);

            return (int)$val;
        }

        return false;
    }

    public function getPost($name)
    {
        if (isset($_POST[$name]) && !empty($_POST[$name])) {
            return $_POST[$name];
        }

        return false;
    }

    public function uri($index)
    {
        if (isset($_GET['uri'])) {
            $uri = explode('/', $_SERVER['REQUEST_URI']);
            if (isset($uri[$index])) {
                return $uri[$index];
            }
        }

        return false;
    }

    public function uriInt($index)
    {
        $val = (int)$this->uri($index);

        return $val;
    }

    public function uriStr($index)
    {
        $val = $this->uri($index);
        if ($val !== false) {
            return preg_replace('/[^a-z0-9\-]/', '', $val);
        }

        return false;
    }
}
