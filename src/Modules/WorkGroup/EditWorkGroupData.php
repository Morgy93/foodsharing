<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\DBConstants\Region\ApplyType;
use Symfony\Component\Validator\Constraints as Assert;

class EditWorkGroupData
{
	/**
	 * @Assert\Type("string")
	 * @Assert\NotBlank()
	 */
	public string $name;

	/**
	 * @Assert\Type("string")
	 */
	public ?string $description;

	/**
	 * @Assert\Type("integer")
	 * @Assert\Range(
	 *     min = 0,
	 *     max = 3
	 * )
	 */
	public int $applyType;

	/**
	 * @Assert\Type("integer")
	 * @Assert\Range(
	 *     min = 0,
	 *     max = 20,
	 *     minMessage = "group.application_requirements.banana_count_errors.min",
	 *     maxMessage = "group.application_requirements.banana_count_errors.max"
	 * )
	 */
	public int $bananaCount;

	/**
	 * @Assert\Type("integer")
	 * @Assert\Range(
	 *     min = 0,
	 *     max = 100
	 * )
	 */
	public int $fetchCount;

	/**
	 * @Assert\Type("integer")
	 * @Assert\Range(
	 *     min = 0,
	 *     max = 52
	 * )
	 */
	public int $weekNum;

	/**
	 * @Assert\Type("string")
	 * @Assert\Regex("?^images/[[:alnum:]/]+\.(jpg|png|gif)$?")
	 * @Assert\File()
	 */
	public ?string $photo;

	public static function fromGroup(array $group): self
	{
		$workGroupRequest = new self();
		$workGroupRequest->name = $group['name'];
		$workGroupRequest->description = $group['teaser'] ?? '';
		$workGroupRequest->applyType = $group['apply_type'];
		$workGroupRequest->bananaCount = $group['banana_count'];
		$workGroupRequest->fetchCount = $group['fetch_count'];
		$workGroupRequest->weekNum = $group['week_num'];
		$workGroupRequest->photo = $group['photo'] ? 'images/' . $group['photo'] : null;

		return $workGroupRequest;
	}

	public function toGroup(): array
	{
		/*
		 * ToDo this seems a bit hacky but works for now as toArray is always
		 * called to get the data out of this data object.
		 * A separate transformation method might make sense.
		 */
		if ($this->applyType != ApplyType::REQUIRES_PROPERTIES) {
			$this->bananaCount = 0;
			$this->fetchCount = 0;
			$this->weekNum = 0;
		}

		$photo = $this->photo ? explode('images/', $this->photo, 2)[1] : null;

		$res = [
			'name' => $this->name,
			'teaser' => $this->description ?? '',
			'apply_type' => $this->applyType,
			'banana_count' => $this->bananaCount,
			'fetch_count' => $this->fetchCount,
			'week_num' => $this->weekNum,
			'photo' => $photo
		];

		return $res;
	}
}
