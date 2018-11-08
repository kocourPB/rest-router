<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Exception\Api;

use Nette\Http\IResponse;
use Packeto\RestRouter\Exception\Api\ApiException;

class SchemaValidatorException extends ApiException
{

	public const INVALID_JSON_SCHEME = 901;

	public const INVALID_PARAMETERS = 902;

	public const INVALID_SCHEMA_PATH = 903;

	/**
	 * @var array
	 */
	protected $errors = [];


	public function __construct(string $message = '', int $errorCode, array $errors = [], ?\Throwable $previousException = null)
	{
		parent::__construct($message, $errorCode, IResponse::S400_BAD_REQUEST, $previousException);
		$this->errors = $errors;
		$this->success = false;
	}


	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}


	public function buildMessage(): array
	{
		$message = parent::buildMessage();

		if (!empty($this->getErrors())) {
			$message['error']['validation_errors'] = $this->getErrors();
		}

		return $message;
	}
}
