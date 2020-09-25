<?php

namespace WMDE\Fundraising\Frontend\BucketTesting;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;

class BucketTestingContextFactory {
	public const ENTITY_NAMESPACE = 'WMDE\Fundraising\Frontend\BucketTesting\Domain\Model';

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/config/DoctrineClassMapping';

	public function newMappingDriver(): MappingDriver {
		// We're only calling this for the side effect of adding Mapping/Driver/DoctrineAnnotations.php
		// to the AnnotationRegistry. When AnnotationRegistry is deprecated with Doctrine Annotations 2.0,
		// use $this->annotationReader instead
		return new XmlDriver( self::DOCTRINE_CLASS_MAPPING_DIRECTORY );
	}
}
