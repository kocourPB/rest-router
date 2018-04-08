<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Exception;

use Nette\Http\IResponse;
use Packeto\RestRouter\Exception\Api\ApiException;

class AuthenticationException extends ApiException
{

	public const MISSING_HEADER_VALUE = 801;

	public const BAD_AUTHANTICATION = 802;


	public function __construct(string $message = '', int $errorCode, array $errors = [])
	{
		parent::__construct($message, $errorCode, IResponse::S401_UNAUTHORIZED);
	}
}
