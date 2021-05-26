<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;

class AutocompleteContextFactory {
	public const ENTITY_NAMESPACE = 'WMDE\Fundraising\Frontend\Autocomplete\Domain\Model';

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/config/DoctrineClassMapping';

	public function newMappingDriver(): MappingDriver {
		return new XmlDriver( self::DOCTRINE_CLASS_MAPPING_DIRECTORY );
	}
}
