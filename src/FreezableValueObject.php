<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
trait FreezableValueObject {

	private $isFrozen = false;

	public function freeze(): self {
		$this->isFrozen = true;
		return $this;
	}

	/**
	 * @throws \RuntimeException
	 */
	protected function assertIsWritable(): self {
		if ( $this->isFrozen ) {
			throw new \RuntimeException( 'Cannot write to a frozen object!' );
		}
		return $this;
	}

	/**
	 * Throws an exception if any of the fields have null as value.
	 *
	 * @throws \RuntimeException
	 */
	public function assertNoNullFields(): self {
		foreach ( get_object_vars( $this ) as $fieldName => $fieldValue ) {
			if ( $fieldValue === null ) {
				throw new \RuntimeException( "Field '$fieldName' cannot be null" );
			}
		}
		return $this;
	}

}