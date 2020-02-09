<?php

namespace Foodsharing\Lib;

class WebSocketSender
{
	public function sendSock($fsid, $app, $method, $options)
	{
		$query = http_build_query([
			'u' => $fsid, // user id
			'a' => $app, // app
			'm' => $method, // method
			'o' => json_encode($options) // options
		]);
		file_get_contents(SOCK_URL . '?' . $query);
	}

	public function sendSockMulti($fsids, $app, $method, $options)
	{
		$query = http_build_query([
			'us' => join(',', $fsids), // user ids
			'a' => $app, // app
			'm' => $method, // method
			'o' => json_encode($options) // options
		]);
		file_get_contents(SOCK_URL . '?' . $query);
	}
}
