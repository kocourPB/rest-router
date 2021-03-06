<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Exception\Api;

use Nette\Http\IResponse;
use Packeto\RestRouter\Exception\Api\ApiException;

class AuthenticationException extends ApiException
{

	public const MISSING_HEADER_VALUE = 801;

	public const BAD_AUTHENTICATION = 802;


	public function __construct(string $message = '', int $errorCode, array $errors = [], ?\Throwable $previousException = null)
	{
		parent::__construct($message, $errorCode, IResponse::S401_UNAUTHORIZED, $previousException);
	}
}
