<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Unit;

use InvalidArgumentException;
use RuntimeException;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\BankDataConverter;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;

/**
 * @covers WMDE\Fundraising\Frontend\PaymentContext\Domain\BankDataConverter
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer <christoph.fischer@wikimedia.de >
 */
class BankDataConverterTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		if ( !function_exists( 'lut_init' ) ) {
			$this->markTestSkipped( 'The konto_check needs to be installed!' );
		}
	}

	public function testWhenUsingConfigLutPath_constructorCreatesConverter() {
		$this->assertInstanceOf( BankDataConverter::class, $this->newBankDataConverter() );
	}

	/**
	 * @expectedException \WMDE\Fundraising\Frontend\PaymentContext\Domain\BankDataLibraryInitializationException
	 */
	public function testGivenNotExistingBankDataFile_constructorThrowsException() {
		$this->newBankDataConverter( '/foo/bar/awesome.data' );
	}

	/**
	 * @dataProvider ibanTestProvider
	 */
	public function testWhenGivenInvalidIban_converterThrowsException( $ibanToTest ) {
		$bankConverter = $this->newBankDataConverter();

		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Provided IBAN should be valid' );
		$bankConverter->getBankDataFromIban( new Iban( $ibanToTest ) );
	}

	public function ibanTestProvider() {
		return [
			[ '' ],
			[ 'DE120105170648489892' ],
			[ 'DE1048489892' ],
			[ 'BE125005170648489890' ],
		];
	}

	/**
	 * @dataProvider ibanTestProvider
	 */
	public function testWhenGivenInvalidIban_validateIbanReturnsFalse( $ibanToTest ) {
		$bankConverter = $this->newBankDataConverter();

		$this->assertFalse( $bankConverter->validateIban( new Iban( $ibanToTest ) ) );
	}

	public function testWhenGivenValidIban_converterReturnsBankData() {
		$bankConverter = $this->newBankDataConverter();

		$bankData = new BankData();
		$bankData->setBankName( 'ING-DiBa' );
		$bankData->setAccount( '0648489890' );
		$bankData->setBankCode( '50010517' );
		$bankData->setBic( 'INGDDEFFXXX' );
		$bankData->setIban( new Iban( 'DE12500105170648489890' ) );

		$this->assertEquals(
			$bankData,
			$bankConverter->getBankDataFromIban( new Iban( 'DE12500105170648489890' ) )
		);
	}

	public function testWhenGivenValidNonDEIban_converterReturnsIBAN() {
		$bankConverter = $this->newBankDataConverter();

		$bankData = new BankData();
		$bankData->setBankName( '' );
		$bankData->setAccount( '' );
		$bankData->setBankCode( '' );
		$bankData->setBic( '' );
		$bankData->setIban( new Iban( 'BE68844010370034' ) );

		$this->assertEquals(
			$bankData,
			$bankConverter->getBankDataFromIban( new Iban( 'BE68844010370034' ) )
		);
	}

	public function testWhenGivenValidIban_validateIbanReturnsTrue() {
		$bankConverter = $this->newBankDataConverter();

		$this->assertTrue( $bankConverter->validateIban( new Iban( 'DE12500105170648489890' ) ) );
		$this->assertTrue( $bankConverter->validateIban( new Iban( 'BE68844010370034' ) ) );
	}

	/**
	 * @dataProvider accountTestProvider
	 */
	public function testWhenGivenInvalidAccountData_converterThrowsException( $accountToTest, $bankCodeToTest ) {
		$bankConverter = $this->newBankDataConverter();

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not get IBAN' );
		$bankConverter->getBankDataFromAccountData( $accountToTest, $bankCodeToTest );
	}

	public function accountTestProvider() {
		return [
			[ '', '' ],
			[ '0648489890', '' ],
			[ '0648489890', '12310517' ],
			[ '1234567890', '50010517' ],
			[ '', '50010517' ],
		];
	}

	public function testWhenGivenValidAccountData_converterReturnsBankData() {
		$bankConverter = $this->newBankDataConverter();

		$bankData = new BankData();
		$bankData->setBankName( 'ING-DiBa' );
		$bankData->setAccount( '0648489890' );
		$bankData->setBankCode( '50010517' );
		$bankData->setBic( 'INGDDEFFXXX' );
		$bankData->setIban( new Iban( 'DE12500105170648489890' ) );

		$this->assertEquals(
			$bankData,
			$bankConverter->getBankDataFromAccountData( '0648489890', '50010517' )
		);
	}

	private function newBankDataConverter( string $filePath = 'res/blz.lut2f' ) {
		return new BankDataConverter( $filePath );
	}

}
