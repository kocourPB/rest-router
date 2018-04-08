<?php

declare(strict_types=1);

namespace Packeto\RestRouter\Common\Annotations;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class JsonSchema
{

	/**
	 * @var string|null
	 */
	protected $file = null;


	public function __construct(array $data = [])
	{
		if (isset($data['value'])) {
			$this->file = $data['value'];
		}
	}


	/**
	 * @return null|string
	 */
	public function getFile(): ?string
	{
		return $this->file;
	}
}
