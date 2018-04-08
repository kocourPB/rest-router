<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Common\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class DTO
{

	/**
	 * @var string|null
	 */
	protected $dto = null;


	public function __construct(array $data = [])
	{
		if (isset($data['value'])) {
			$this->dto = $data['value'];
		}
	}


	/**
	 * @return null|string
	 */
	public function getDto(): ?string
	{
		return $this->dto;
	}
}
