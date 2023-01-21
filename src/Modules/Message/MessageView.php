<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\View;

final class MessageView extends View
{
    public function index(): string
    {
        return $this->vueComponent('message', 'MessagePage');
    }
}
