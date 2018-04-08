<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Exception\Api;

use Packeto\RestRouter\Exception\RuntimeException;

class ApiException extends RuntimeException
{

	/**
	 * @var bool
	 */
	protected $success = false;

	/**
	 * @var int
	 */
	protected $errorCode;


	public function __construct(string $message = '', int $errorCode, int $httpCode = 400)
	{
		parent::__construct($message, $httpCode, null);
		$this->errorCode = $errorCode;
	}


	/**
	 * @return int
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}


	/**
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->success;
	}


	public function buildMessage(): array
	{
		return [
			'success' => $this->isSuccess(),
			'error' => [
				'message' => $this->getMessage(),
				'code' => $this->getErrorCode(),
			],
		];
	}

}
