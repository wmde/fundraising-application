<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

class BankDataPresenter {
	private const BANK_DATA_KEYS = [ 'iban' => '', 'bic' => '', 'bankname' => '' ];

	/**
	 * @param array<string,scalar> $bankData
	 * @return array{iban?:string,bic?:string,bankname?:string}
	 */
	public static function getBankDataArray( array $bankData ): array {
		$filteredData = array_intersect_key( $bankData, self::BANK_DATA_KEYS );
		$stringData = array_map( 'strval', $filteredData );
		return array_merge( self::BANK_DATA_KEYS, $stringData );
	}
}
