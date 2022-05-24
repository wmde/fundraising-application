<?php

namespace WMDE\Fundraising\Frontend\BucketTesting;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;

class BucketTestingContextFactory {
	public const ENTITY_NAMESPACE = 'WMDE\Fundraising\Frontend\BucketTesting\Domain\Model';

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/config/DoctrineClassMapping';

	public function newMappingDriver(): MappingDriver {
		return new XmlDriver( self::DOCTRINE_CLASS_MAPPING_DIRECTORY );
	}
}
