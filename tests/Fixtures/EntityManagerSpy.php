<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use Doctrine\ORM\EntityManager;

/**
 * TODO: EntityManager is now a final class which Stan doesn't like. We
 *       should change the tests at some point
 */
/** @phpstan-ignore-next-line */
class EntityManagerSpy extends EntityManager {

	/** @var mixed */
	private $entity;

	public function __construct() {
	}

	public function persist( mixed $object ): void {
		$this->entity = $object;
	}

	public function flush( mixed $entity = null ): void {
	}

	public function getEntity(): ?object {
		return $this->entity;
	}
}
