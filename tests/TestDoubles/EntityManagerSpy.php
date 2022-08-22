<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\TestDoubles;

use Doctrine\ORM\EntityManager;

class EntityManagerSpy extends EntityManager {

	/** @var mixed */
	private $entity;

	public function __construct() {
	}

	/**
	 * @param mixed $entity
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function persist( $entity ): void {
		$this->entity = $entity;
	}

	/**
	 * @param null $entity
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function flush( $entity = null ) {
	}

	public function getEntity(): ?object {
		return $this->entity;
	}
}
