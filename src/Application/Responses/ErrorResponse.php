<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Application\Responses;

use Nette;
use Packeto\RestRouter\Exception\Api\ApiException;
use Tracy\Debugger;

class ErrorResponse implements Nette\Application\IResponse
{

	/**
	 * @var \Throwable|\Exception
	 */
	private $exception;

	/**
	 * @var null|string
	 */
	private $contentType;


	public function __construct($exception, $contentType = null)
	{
		$this->exception = $exception;
		$this->contentType = $contentType ? $contentType : 'application/json';
	}


	function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		if ($this->exception instanceof ApiException) {
			$httpResponse->setCode($this->exception->getCode());
			$payload = $this->exception->buildMessage();
		} else {
			$httpResponse->setCode(500);
			$payload = [
				'success' => false,
				'error' => [
					'message' => (string) $this->exception->getMessage(),
				],
			];
		}

		$httpResponse->setContentType($this->contentType, 'utf-8');
		echo Nette\Utils\Json::encode($payload);
	}
}
