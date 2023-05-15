<?php

namespace Foodsharing\Lib;

use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\InfluxMetrics;
use Foodsharing\Modules\Core\View;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\FlashMessageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\TranslationHelper;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Compatibility layer to make porting old "Control" based controllers to new "Symfony" style controllers easier.
 * Any controller based on this also opts into the setup code that used to live in IndexController.
 *
 * @see RenderControllerSetupSubscriber
 */
abstract class FoodsharingController extends AbstractController
{
    protected View $view;
    // $sub was deliberately left out in this compatibility layer for the time being.
    // However, a replacement or better solution for its behavior will be necessary for porting some controllers.

    protected PageHelper $pageHelper;
    protected Mem $mem;
    protected Session $session;
    protected Utils $v_utils;
    private InfluxMetrics $metrics;
    protected EmailHelper $emailHelper;
    protected FlashMessageHelper $flashMessageHelper;
    protected RouteHelper $routeHelper;
    protected TranslationHelper $translationHelper;
    protected TranslatorInterface $translator;

    /**
     * @throws \Exception if the inheriting class does not end with "Controller"
     */
    public function __construct()
    {
        /*
         * This is not ideal, but easier than getting all these services through DI.
         * Inheriting classes would need to manually pass all of them through,
         * leading to a lot of boilerplate.
         * Maybe there is a more optimal way to go about this, but this will do for now.
         */
        global $container;

        $this->mem = $container->get(Mem::class);
        $this->session = $container->get(Session::class);
        $this->v_utils = $container->get(Utils::class);
        $this->metrics = $container->get(InfluxMetrics::class);
        $this->pageHelper = $container->get(PageHelper::class);
        $this->emailHelper = $container->get(EmailHelper::class);
        $this->routeHelper = $container->get(RouteHelper::class);
        $this->flashMessageHelper = $container->get(FlashMessageHelper::class);
        $this->translationHelper = $container->get(TranslationHelper::class);
        $this->translator = $container->get('translator'); // TODO TranslatorInterface is an alias

        $reflection = new ReflectionClass($this);
        $className = $reflection->getShortName();

        // $sub would be set up here.
        // as mentioned above, it and its behavior are not implemented

        /*
         * This will make sure all controllers are suffixed 'Controller'.
         * It also makes it relatively easy for a developer to catch the (unlikely) mistake.
         * Also, when porting an old "Control" to a new Symfony "Controller" class,
         * it makes it easy to have both working at the same time for comparisons.
         */
        $pos = strpos($className, 'Controller');
        if ($pos === false) {
            throw new \Exception('Please rename the controller "' . $className . '" to end with "Controller".');
        }

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

        $this->metrics->addPageStatData(['controller' => $className]);
    }

    /**
     * Previously, most controllers relied on IndexController actually rendering the website.
     * They mostly talk to pageHelper, which is then used like this to generate the view data for the desired twig template.
     * There are two things to be mentioned here:
     * - MapControl and MessageControl are the only controllers changing the template from 'default'.
     *   They can use this method's $template argument.
     * - Some controllers call Control::render, which is different from AbstractController::render.
     *
     * If a controller method only interacts with PageHelper (directly, or indirectly through a View class that gets it through DI),
     * all you have to do to port it (rendering wise) is call this method at the end, and then return its result.
     *
     * @param string $template which template should be used when rendering the website
     */
    protected function renderGlobal(string $template = 'default'): Response
    {
        return $this->render('layouts/' . $template . '.twig', $this->pageHelper->generateAndGetGlobalViewData());
    }
}
