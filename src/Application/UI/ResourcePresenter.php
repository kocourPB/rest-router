<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Application\UI;

use Exception;
use Nette\Application\AbortException;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Utils\Json;
use Packeto\RestRouter\Application\Responses\ErrorResponse;
use Packeto\RestRouter\Application\Responses\SuccessResponse;
use Packeto\RestRouter\AnnotationsResolver;
use Packeto\RestRouter\Command\Exception\CommandDTONotFoundException;
use Packeto\RestRouter\Command\ICommandDTO;
use Packeto\RestRouter\Exception\Api\ApiException;
use Packeto\RestRouter\Exception\Api\ClientErrorException;
use Packeto\RestRouter\Security\IAuthenticator;
use Shopee\ApiModule\Presenters\CreateUserDTO;
use Throwable;

abstract class ResourcePresenter extends Presenter
{

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var callable[]
	 */
	public $onRequest = [];

	/**
	 * @var callable[]
	 */
	public $onResponse = [];

	/**
	 * @var callable[]
	 */
	public $onError = [];

	/**
	 * @var array
	 */
	private $globalParams;

	/**
	 * @var IResponse
	 */
	private $response;

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


	public function run(Request $request)
	{
		$this->request = $request;

		$this->onRequest($this, $this->getHttpRequest(), $request);
		$this->setParent($this->getParent(), $request->getPresenterName());

		$this->initGlobalParameters();
		$this->checkRequirements($this->getReflection());
		$this->onStartup($this);

		try {
			$this->tryCall($this->formatActionMethod($this->action), $this->params);
		} catch (Exception|Throwable $e) {

			if (!$e instanceof AbortException) {
				try {
					$this->onError($e);
					$this->sendErrorResponse($e);
				} catch (AbortException $e) {
				}
			}

			$this->onShutdown($this, $this->response);
			$this->shutdown($this->response);

			$this->onResponse($this->response);

			return $this->response;
		}
	}


	public function getAuthenticator(): IAuthenticator
	{
		return $this->annotationsResolver->getAuthenticator();
	}


	public function getRequestObject()
	{
		return Json::decode($this->getHttpRequest()->getRawBody());
	}


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


	/* ================================================================
	 * 	 					    IPresenter API
	 * ================================================================ */

	private function initGlobalParameters()
	{
		// init $this->globalParams
		$this->globalParams = [];
		$selfParams = [];

		$params = $this->request->getParameters();

		foreach ($params as $key => $value) {
			if (!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+\z)[a-z0-9_]+)\z#i', $key, $matches)) {
				continue;
			} elseif (!$matches[1]) {
				$selfParams[$key] = $value;
			} else {
				$this->globalParams[substr($matches[1], 0, -1)][$matches[2]] = $value;
			}
		}

		// init & validate $this->action & $this->view
		$this->changeAction(isset($selfParams[self::ACTION_KEY]) ? $selfParams[self::ACTION_KEY] : self::DEFAULT_ACTION);

		$this->loadState($selfParams);
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


	public function sendResponse(\Nette\Application\IResponse $response)
	{
		$this->response = $response;
		$this->onResponse($this->response);

		parent::sendResponse($response);
	}
}
