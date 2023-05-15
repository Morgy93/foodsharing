<?php

namespace Helper;

use Codeception\Module;
use Codeception\Module\Symfony;

class Container extends Module
{
    private Symfony $symfonyModule;

    final public function _initialize(): void
    {
        parent::_initialize();

        $this->symfonyModule = $this->getModule('Symfony');

        // Persist RealPDO (see config/packages/services.yaml) between tests.
        // PHP's PDO only closes its underlying connection if no references to it exist,
        // and is not guranteed to do so immediately.
        // Usually, services are not kept between tests by Symfony,
        // which means we end up with a dangling DB connection for every test executed.
        // At some point, we hit MariaDB's connection limit,
        // which makes every test fail immediately.
        $this->symfonyModule->persistService('RealPDO');

        // needed so Control-based classes (which rely on this global currently)
        // can be used in unit tests
        global $container;
        $container = $this->symfonyModule->_getContainer();
    }

    public function get($id)
    {
        return $this->symfonyModule->grabService($id);
    }
}
