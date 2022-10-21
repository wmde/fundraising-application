<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Symfony\Component\HttpFoundation\ParameterBag;

class ParameterBagEncodingConverter {
	public static function convert( ParameterBag $input, string $fromEncoding, string $toEncoding = 'UTF-8' ): ParameterBag {
		return new ParameterBag(
			array_map(
				fn( $value ) => iconv( $fromEncoding, $toEncoding, $value ),
				$input->all()
			),
		);
	}
}
