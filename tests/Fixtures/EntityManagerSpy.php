<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use Doctrine\ORM\Decorator\EntityManagerDecorator;

class EntityManagerSpy extends EntityManagerDecorator {
	private ?object $entity = null;

	public function persist( object $object ): void {
		$this->entity = $object;
	}

	public function getEntity(): ?object {
		return $this->entity;
	}
}
