<?php

namespace Foodsharing\Lib\Xhr;

class XhrResponses
{
    public const PERMISSION_DENIED = ['permission_denied'];

    public function fail_generic()
    {
        return ['status' => 0];
    }
}
