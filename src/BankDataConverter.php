<?php

namespace WMDE\Fundraising\Frontend;

use WMDE\Fundraising\Frontend\Domain\BankData;
use WMDE\Fundraising\Frontend\Domain\Iban;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer <christoph.fischer@wikimedia.de >
 */
class BankDataConverter {

	private $lutPath;

	/**
	 * @param string $lutPath
	 * @throws BankDataLibraryInitializationException
	 */
	public function __construct( string $lutPath ) {
		$this->lutPath = $lutPath;
		if ( lut_init( $this->lutPath ) !== 1 ) {
			throw new BankDataLibraryInitializationException( $this->lutPath );
		}
	}

	/**
	 * @param string $account
	 * @param string $bankCode
	 * @return bool|BankData
	 */
	public function getBankDataFromAccountData( string $account, string $bankCode ) {
		$bankData = new BankData();
		$iban = iban_gen( $bankCode, $account );
		if ( !$iban ) {
			return false;
		}

		$bankData->setIban( $iban );
		if ( $bankData->getIban() ) {
			$bankData->setBic( iban2bic( $bankData->getIban() ) );

			$bankData->setAccount( $account );
			$bankData->setBankCode( $bankCode );
			$bankData->setBankName( $this->bankNameFromBankCode( $bankData->getBankCode() ) );

			return $bankData;
		}

		return false;
	}

	/**
	 * @param Iban $iban
	 * @return bool|BankData
	 */
	public function getBankDataFromIban( Iban $iban ) {
		if ( !$this->validateIban( $iban ) ) {
			return false;
		}

		$bankData = new BankData();
		$bankData->setIban( $iban->toString() );

		if ( $iban->getCountryCode() === 'DE' && $this->validateIban( $iban ) ) {
			$bankData->setBic( iban2bic( $iban->toString() ) );

			$bankData->setAccount( $iban->accountNrFromDeIban() );
			$bankData->setBankCode( $iban->bankCodeFromDeIban() );
			$bankData->setBankName( $this->bankNameFromBankCode( $bankData->getBankCode() ) );

		}

		return $bankData;
	}

	private function bankNameFromBankCode( string $bankCode ): string {
		return utf8_encode( lut_name( $bankCode ) );
	}

	public function validateIban( Iban $iban ): bool {
		$ret = iban_check( $iban->toString() );
		return $ret > 0;
	}
}
