<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Exception\Api;

class ClientErrorException extends ApiException
{

	public function __construct(string $message = '', int $errorCode, int $httpCode = 400)
	{
		parent::__construct($message, $errorCode, $httpCode);
		$this->success = false;
	}
}
