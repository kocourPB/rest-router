# RestRouter
Basic extension for REST API routing, with annotations helpers.

## Installation

Install dependency over Composer:
```sh
composer require packeto/rest-router
```

Register extension in configuration file:
```yaml
extensions:
  apirouter: Packeto\RestRouter\DI\RestRouterExtension
```

Configure JSON Schemas directory:
```yaml
apirouter:
  jsonSchemasPath: %appDir%/../json-schema
```

## Usage

### ResourcePresenter
Routes are defined in Presenter which extends from `ResourcePresenter` provided by extension:
```php
/**
 * @ApiRoute(
 *        "/api/v1/hello",
 *        presenter="Api:Hello"
 * )
 */
class HelloPresenter extends ResourcePresenter
{

	/**
	 * @Authenticated
	 * @JsonSchema("users/hello.json")
	 * @DTO("API\Commands\DTO\HelloDTO")
	 */
	public function actionCreate()
	{
		// Instance of API\Commands\DTO\HelloDTO
		$dto = $this->getCommandDTO();
  
		$this->sendSuccessResponse();
	}
}
```

### JSON Schema validation
Request (`POST`, `PUT`, `PATCH`) can be validated with [JSON schema](http://json-schema.org/). You must have cofingured `jsonSchemasPath` in your config.

Then create JSON Schema file in this folder, e.g. `test.json` and method annotation place this file name:
```
@JsonSchema("test.json")
```

And befero execution, input will be validated. Simple.


### Request Authentication
Extensions provides you `@Authenticated` annotation, which authenticate every request pointing on endpoind. You must implement your own logic by implementing `IAuthenticator`.

#### Example of implementation:
```php
use Nette\Http\IRequest;
use Packeto\RestRouter\Exception\Api\AuthenticationException;
use Packeto\RestRouter\Security\IAuthenticator;

class Authenticator implements IAuthenticator
{

	private const AUTHENTICATION_HEADER = 'X-Api-Key';


	/**
	 * @param IRequest $request
	 * @return void
	 * @throws AuthenticationException
	 */
	public function authenticate(IRequest $request): void
	{
		if (is_null($request->getHeader(self::AUTHENTICATION_HEADER, null))) {
			throw new AuthenticationException(
				sprintf('Header %s is empty, or is not provided.', self::AUTHENTICATION_HEADER),
				AuthenticationException::MISSING_HEADER_VALUE
			);
		}

		return;
	}
}
```

Then register your API authenticator as service in your configuration.
