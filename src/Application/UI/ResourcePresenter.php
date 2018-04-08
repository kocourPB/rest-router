<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Application\UI;

use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Json;
use Packeto\RestRouter\Application\Responses\ErrorResponse;
use Packeto\RestRouter\Application\Responses\SuccessResponse;
use Packeto\RestRouter\AnnotationsResolver;
use Packeto\RestRouter\Command\Exception\CommandDTONotFoundException;
use Packeto\RestRouter\Command\ICommandDTO;
use Shopee\ApiModule\Presenters\CreateUserDTO;
use Throwable;

abstract class ResourcePresenter extends Presenter
{

	/**
	 * @var AnnotationsResolver
	 */
	private $annotationsResolver;

	/**
	 * @var ICommandDTO|null
	 */
	private $commandDTO = null;


	/**
	 * @param AnnotationsResolver $annotationsResolver
	 */
	public function injectStuff(AnnotationsResolver $annotationsResolver)
	{
		$this->annotationsResolver = $annotationsResolver;
	}


	protected function startup()
	{
		parent::startup();
		$this->autoCanonicalize = false;

		try {
			$this->tryCall($this->formatActionMethod($this->action), $this->params);
		} catch (Exception|Throwable $e) {
			if ($e instanceof AbortException) {
				return;
			}
			$this->sendErrorResponse($e);
		}
	}


	public function getRequestObject()
	{
		return Json::decode($this->getHttpRequest()->getRawBody());
	}


	public function checkRequirements($element)
	{
		parent::checkRequirements($element);
		$this->annotationsResolver->resolveAnnotations($element, $this->getHttpRequest());

		if ($element->hasAnnotation('DTO')) {
			$dto = str_replace('"', '', $element->getAnnotation('DTO'));

			if (class_exists($dto) === false) {
				throw new CommandDTONotFoundException(
					sprintf('Command DTO %s was not found.', $dto)
				);
			}

			$this->commandDTO = call_user_func($dto . '::fromRequest', $this->getRequestObject());
		}
	}


	/**
	 * @return null|ICommandDTO
	 */
	public function getCommandDTO(): ?ICommandDTO
	{
		return $this->commandDTO;
	}


	protected function sendErrorResponse($exception)
	{
		$this->sendResponse(new ErrorResponse($exception));
	}


	protected function sendSuccessResponse($data = null, $httpCode = null)
	{
		$this->sendResponse(new SuccessResponse($data, $httpCode));
	}
}
