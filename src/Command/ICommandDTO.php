<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Command;

interface ICommandDTO
{

	public static function fromRequest($data): ICommandDTO;
}