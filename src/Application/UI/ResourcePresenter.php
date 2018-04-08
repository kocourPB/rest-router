<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Application\UI;

use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Packeto\RestRouter\Application\Responses\ErrorResponse;
use Packeto\RestRouter\Application\Responses\SuccessResponse;
use Packeto\RestRouter\AnnotationsResolver;
use Throwable;

abstract class ResourcePresenter extends Presenter
{

	/**
	 * @var AnnotationsResolver
	 */
	private $annotationsResolver;


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


	public function checkRequirements($element)
	{
		parent::checkRequirements($element);
		$this->annotationsResolver->resolveAnnotations($element, $this->getHttpRequest());
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
