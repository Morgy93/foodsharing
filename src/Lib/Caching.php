<?php

namespace Foodsharing\Lib;

use Foodsharing\Lib\Db\Mem;
use Foodsharing\Modules\Core\InfluxMetrics;

class Caching
{
    private array $cacheRules;
    private string $cacheMode;

    public function __construct(
        $cacheRules,
        private readonly Session $session,
        private readonly Mem $mem,
        private readonly InfluxMetrics $metrics,
    ) {
        $this->cacheRules = $cacheRules;
        $this->cacheMode = $this->session->mayRole() ? 'u' : 'g';
    }

    public function lookup(): void
    {
        if ($this->shouldCache() && ($page = $this->mem->getPageCache($this->session->id())) !== false && !isset($_GET['flush'])) {
            $this->metrics->addPageStatData(['cached' => 1]);
            if ($page[0] == '{' || $page[0] == '[') {
                // just assume it's an JSON, to prevent the browser from interpreting it as
                // HTML, which could result in XSS possibilities
                /* this part goes together with /xhr and /xhrapp. It is not needed anymore when they are gone. */
                header('Content-Type: application/json');
            }
            echo $page;
            exit;
        } else {
            $this->metrics->addPageStatData(['cached' => 0]);
        }
    }

    public function shouldCache(): bool
    {
        return isset($this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode]);
    }

    public function cache($content): void
    {
        $this->mem->setPageCache(
            $content,
            $this->cacheRules[$_SERVER['REQUEST_URI']][$this->cacheMode],
            $this->session->id()
        );
    }
}
