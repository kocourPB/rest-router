<?php

declare(strict_types=1);

namespace Packeto\RestRouter;

use Nette\Http\IRequest;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Packeto\RestRouter\Exception\SchemaValidatorException;
use Packeto\RestRouter\Security\IAuthenticator;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class AnnotationsResolver
{

	use SmartObject;

	/**
	 * @var IRequest
	 */
	private $request;

	/**
	 * @var IAuthenticator
	 */
	private $authenticator;

	/**
	 * @var array
	 */
	private $config = [];


	public function __construct(IAuthenticator $authenticator)
	{
		$this->authenticator = $authenticator;
	}


	public function resolveAnnotations($element, IRequest $request)
	{
		$this->request = $request;

		if ($element->hasAnnotation('JsonSchema')) {
			$this->validateJsonSchema($element->getAnnotation('JsonSchema'));
		}

		if ($element->hasAnnotation('Authenticated')) {
			$this->authenticateRequest();
		}
	}


	private function authenticateRequest()
	{
		$this->authenticator->authenticate($this->request);
	}


	private function validateJsonSchema($element)
	{
		if (is_null($this->config['jsonSchemasPath'])) {
			return;
		}

		if (in_array($this->request->getMethod(), [IRequest::POST, IRequest::PATCH, IRequest::PUT])) {
			try {
				$schema = @file_get_contents($this->config['jsonSchemasPath'] . '/' . str_replace('"', '', $element));
				if ($schema === false) {
					throw new SchemaValidatorException(
						sprintf('Schema %s was not found in %s.', str_replace('"', '', $element), $this->config['jsonSchemasPath']),
						SchemaValidatorException::INVALID_SCHEMA_PATH
					);
				}
				$schema = Json::decode($schema);
			} catch (JsonException $e) {
				throw new SchemaValidatorException(
					sprintf('Schema %s is not a valid JSON scheme.', str_replace('"', '', $element)),
					SchemaValidatorException::INVALID_JSON_SCHEME
				);
			}

			$validator = new Validator(Json::decode($this->request->getRawBody()), $schema);

			if ($validator->fails()) {
				$errors = array_map(function (ValidationError $error): string {
					return sprintf('%s: %s', $error->getDataPath(), $error->getMessage());
				}, $validator->errors());

				throw new SchemaValidatorException(
					'Input parameters are not valid.',
					SchemaValidatorException::INVALID_PARAMETERS,
					$errors
				);
			}
		}
	}


	/**
	 * @param array $config
	 */
	public function setConfig(array $config): void
	{
		$this->config = $config;
	}
}
