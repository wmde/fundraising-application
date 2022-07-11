<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Autocomplete;

class AutocompleteContextFactory {

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/config/DoctrineClassMapping';

	/**
	 * @return string[]
	 */
	public function getDoctrineMappingPaths(): array {
		return [ self::DOCTRINE_CLASS_MAPPING_DIRECTORY ];
	}
}
