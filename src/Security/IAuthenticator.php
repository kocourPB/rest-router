<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Security;

use Nette\Http\IRequest;
use Packeto\RestRouter\Exception\AuthenticationException;

interface IAuthenticator
{

	/**
	 * @param IRequest $request
	 * @return void
	 * @throws AuthenticationException
	 */
	public function authenticate(IRequest $request): void;
}
