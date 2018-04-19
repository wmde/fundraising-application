<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\Domain;

use RuntimeException;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;

/**
 * TODO: move to own KontoCheck library?
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class KontoCheckBankDataGenerator implements BankDataGenerator {

	private $ibanValidator;

	/**
	 * @param string $lutPath
	 * @param IbanValidator $ibanValidator
	 *
	 * @throws KontoCheckLibraryInitializationException
	 */
	public function __construct( string $lutPath, IbanValidator $ibanValidator ) {
		$this->ibanValidator = $ibanValidator;

		if ( lut_init( $lutPath ) !== 1 ) {
			throw new KontoCheckLibraryInitializationException( $lutPath );
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
		$bankData->freeze()->assertNoNullFields();

		return $bankData;
	}

	/**
	 * @param Iban $iban
	 * @return BankData
	 * @throws \InvalidArgumentException
	 */
	public function getBankDataFromIban( Iban $iban ): BankData {
		if ( $this->ibanValidator->validate( $iban )->hasViolations() ) {
			throw new \InvalidArgumentException( 'Provided IBAN should be valid' );
		}

		$bankData = new BankData();
		$bankData->setIban( $iban );

		if ( $iban->getCountryCode() === 'DE' ) {
			$bankData->setBic( iban2bic( $iban->toString() ) );

			$bankData->setAccount( $iban->accountNrFromDeIban() );
			$bankData->setBankCode( $iban->bankCodeFromDeIban() );
			$bankData->setBankName( $this->bankNameFromBankCode( $bankData->getBankCode() ) );
		} else {
			$bankData->setBic( '' );

			$bankData->setAccount( '' );
			$bankData->setBankCode( '' );
			$bankData->setBankName( '' );
		}
		$bankData->freeze()->assertNoNullFields();

		return $bankData;
	}

	private function bankNameFromBankCode( string $bankCode ): string {
		return utf8_encode( lut_name( $bankCode ) );
	}

}
