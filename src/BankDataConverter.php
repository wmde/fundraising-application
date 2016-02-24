<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend;

use RuntimeException;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Iban;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
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
	 * @return BankData
	 * @throws RuntimeException
	 */
	public function getBankDataFromAccountData( string $account, string $bankCode ): BankData {
		$bankData = new BankData();
		$iban = iban_gen( $bankCode, $account );

		if ( !$iban ) {
			throw new RuntimeException( 'Could not get IBAN' );
		}

		$bankData->setIban( new Iban( $iban ) );
		$bankData->setBic( iban2bic( $bankData->getIban()->toString() ) );

		$bankData->setAccount( $account );
		$bankData->setBankCode( $bankCode );
		$bankData->setBankName( $this->bankNameFromBankCode( $bankData->getBankCode() ) );

		return $bankData;
	}

	/**
	 * @param Iban $iban
	 * @return BankData
	 * @throws \InvalidArgumentException
	 */
	public function getBankDataFromIban( Iban $iban ): BankData {
		if ( !$this->validateIban( $iban ) ) {
			throw new \InvalidArgumentException( 'Provided IBAN should be valid' );
		}

		$bankData = new BankData();
		$bankData->setIban( $iban );

		if ( $iban->getCountryCode() === 'DE' ) {
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
		return iban_check( $iban->toString() ) > 0;
	}
}
