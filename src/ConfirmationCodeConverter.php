<?php


namespace WMDE\Fundraising\Frontend;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmationCodeConverter {

	public function fromBinaryToReadable( string $binaryString ): string {
		$data = unpack( 'H*', $binaryString );
		return base_convert( array_shift( $data ), 16, 36 );
	}

	public function fromReadableToBinary( string $readableString ): string {
		$hexData = base_convert( $readableString, 36, 16 );
		return pack( 'H*', $hexData );
	}
}