<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Application\Responses;

use Nette;
use Nette\Application\IResponse;
use Nette\Http\IRequest;

class SuccessResponse implements IResponse
{

	/**
	 * @var string|array
	 */
	private $data;

	/**
	 * @var null|string
	 */
	private $contentType;

	/**
	 * @var null|int
	 */
	private $httpCode;

	/**
	 * @var array
	 */
	protected $defaultCodes = [
		IRequest::GET => 200,
		IRequest::POST => 201,
		IRequest::PUT => 200,
		IRequest::HEAD => 200,
		IRequest::DELETE => 200,
		IRequest::PATCH => 200,
	];


	public function __construct($data = null, $httpCode = null, $contentType = null)
	{
		$this->data = $data;
		$this->httpCode = $httpCode;
		$this->contentType = $contentType ? $contentType : 'application/json';
	}


	function send(IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->contentType, 'utf-8');

		if (is_null($this->data)) {
			$httpResponse->setCode(Nette\Http\IResponse::S204_NO_CONTENT);

			return;
		}

		if (is_null($this->httpCode)) {
			$httpResponse->setCode($this->defaultCodes[$httpRequest->getMethod()]);
		}

		$payload = [
			'success' => true,
			'data' => $this->data,
		];

		echo Nette\Utils\Json::encode($payload);
	}
}
