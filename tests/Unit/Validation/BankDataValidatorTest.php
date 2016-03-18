<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\BankDataValidator
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class BankDataValidatorTest extends ValidatorTestCase {

	/**
	 * @dataProvider invalidBankDataProvider
	 */
	public function testFieldsMissing_validationFails( string $iban, string $bic, string $bankName,
		string $bankCode, string $account ) {

		$bankDataValidator = $this->newBankDataValidator();
		$bankData = $this->newBankData( $iban, $bic, $bankName, $bankCode, $account );
		$this->assertFalse( $bankDataValidator->validate( $bankData )->isSuccessful() );
	}

	public function invalidBankDataProvider() {
		return [
			[
				'DB00123456789012345678',
				'',
				'',
				'',
				'',
			],
			[
				'',
				'SCROUSDBXXX',
				'',
				'',
				'',
			],
			[
				'',
				'',
				'Scrooge Bank',
				'',
				'',
			],
			# validation fails for German IBAN and missing obsolete account data
			[
				'DE00123456789012345678',
				'SCROUSDBXXX',
				'Scrooge Bank',
				'',
				'',
			],
		];
	}

	public function testAllRequiredFieldsGiven_validationSucceeds() {
		$bankDataValidator = $this->newBankDataValidator();
		$bankData = $this->newBankData( 'DE00123456789012345678', 'SCROUSDBXXX', 'Scrooge Bank', '12345678', '1234567890' );
		$this->assertTrue( $bankDataValidator->validate( $bankData )->isSuccessful() );
	}

	public function validBankDataProvider() {
		return [
			[
				'DB00123456789012345678',
				'SCROUSDBXXX',
				'Scrooge Bank',
				'',
				'',
			],
			[
				'DE00123456789012345678',
				'SCROUSDBXXX',
				'Scrooge Bank',
				'12345678',
				'1234567890',
			],
		];
	}

	private function newBankData( string $iban, string $bic, string $bankName, string $bankCode, string $account ): BankData {
		return ( new BankData() )
			->setIban( new Iban( $iban ) )
			->setBic( $bic )
			->setBankName( $bankName )
			->setBankCode( $bankCode )
			->setAccount( $account );
	}

	private function newBankDataValidator() {
		$ibanValidatorMock = $this->getMockBuilder( IbanValidator::class )->disableOriginalConstructor()->getMock();
		$ibanValidatorMock->method( 'validate' )
			->willReturn( new ValidationResult() );

		return new BankDataValidator( $ibanValidatorMock );
	}
}
