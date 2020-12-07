<?php

namespace Foodsharing\Modules\Message;

class Conversation
{
	public int $id;
	public ?string $title;
	public ?int $storeId;
	public bool $hasUnreadMessages;
	public array $members;
	public ?Message $lastMessage;
	public ?array $messages = [];
}
