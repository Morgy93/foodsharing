<?php

namespace Foodsharing\Entrypoint;

use Foodsharing\Annotation\DisableCsrfProtection;
use Foodsharing\Lib\Caching;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrMethods;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\InfluxMetrics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class XhrController extends AbstractController
{
    /*
       methods wich are excluded from the CSRF Protection.
       We start with every method and remove one by another
       NEVER ADD SOMETING TO THIS LIST!
    */
    private const csrf_whitelist = [
        // 'bBubble',
        // 'out',
        // 'getRecip',
        // 'continueMail',
        // 'newregion',
        // 'editpickups',
        // 'bezirkTree',
        // 'bteamstatus',
        // 'getBezirk',
        // 'saveBezirk',
        // 'fetchDeny',
        // 'fetchConfirm',
        // 'delPost',
        // 'abortEmail',
    ];

    /**
     * @DisableCsrfProtection CSRF Protection (originally done for the REST API)
     * breaks POST on these entrypoints right now,
     * so this annotation disables it.
     * Note that this entry point still performs CSRF checks on its own,
     * except for what's specified in csrf_whitelist.
     */
    public function __invoke(
        Request $request,
        Session $session,
        Mem $mem,
        InfluxMetrics $influxdb,
        XhrMethods $xhr
    ): Response {
        $session->initIfCookieExists();

        // is this actually used anywhere? (prod?)
        global $g_page_cache;
        if (isset($g_page_cache)) {
            $cache = new Caching($g_page_cache, $session, $mem, $influxdb);
            $cache->lookup();
        }

        $action = $request->query->get('f');

        if ($action === null) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($action, XhrController::csrf_whitelist) && !$session->isValidCsrfHeader()) {
            $response = new Response();
            $response->setProtocolVersion('1.1');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent('CSRF Failed: CSRF token missing or incorrect.');

            return $response;
        }

        $func = 'xhr_' . $action;
        if (!method_exists($xhr, $func)) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $response = new Response();

        $influxdb->addPageStatData(['controller' => $func]);

        $data = $xhr->$func($_GET);

        if ($data === XhrResponses::PERMISSION_DENIED) {
            $response->setProtocolVersion('1.1');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent('Permission denied');

            return $response;
        }

        if (is_array($data)) {
            $data = json_encode($data);
            $response->headers->set('Content-Type', 'application/json');
        }

        // check for page caching
        if (isset($cache) && $cache->shouldCache()) {
            $cache->cache($data);
        }

        $response->setContent($data);

        return $response;
    }
}
