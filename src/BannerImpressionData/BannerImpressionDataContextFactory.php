<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BannerImpressionData;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;

class BannerImpressionDataContextFactory {

	public const ENTITY_NAMESPACE = 'WMDE\Fundraising\Frontend\DoctrineClassMapping\Domain\Model';

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/config/DoctrineClassMapping';

	public function newMappingDriver(): MappingDriver {
		return new XmlDriver( self::DOCTRINE_CLASS_MAPPING_DIRECTORY );
	}
}
