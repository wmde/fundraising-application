<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\BankData;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\BankDataConverter
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer <christoph.fischer@wikimedia.de
 */
class BankDataConverterTest extends \PHPUnit_Framework_TestCase {

	/** @var FunFunFactory */
	private $factory;

	public function setUp() {
		if ( !function_exists( 'lut_init' ) ) {
			$this->markTestSkipped( 'The konto_check needs to be installed!' );
		}
		$this->factory = TestEnvironment::newInstance()->getFactory();
	}

	public function testWhenUsingConfigLutPath_constructorCreatesConverter() {
		$this->assertInstanceOf( BankDataConverter::class, $this->factory->newBankDataConverter() );
	}

	/**
	 * @expectedException \WMDE\Fundraising\Frontend\BankDataLibraryInitializationException
	 */
	public function testGivenNotExistingBankDataFile_constructorThrowsException() {
		new BankDataConverter( '/foo/bar/awesome.data' );
	}

	/**
	 * @dataProvider ibanTestProvider
	 */
	public function testWhenGivenInvalidIban_converterReturnsFalse( $ibanToTest ) {
		$bankConverter = $this->factory->newBankDataConverter();

		$this->assertFalse( $bankConverter->getBankDataFromIban( $ibanToTest ) );
	}

	public function ibanTestProvider() {
		return array(
			array( '' ),
			array( 'DE120105170648489892' ),
			array( 'DE1048489892' ),
			array( 'BE125005170648489890' ),
		);
	}

	/**
	 * @dataProvider ibanTestProvider
	 */
	public function testWhenGivenInvalidIban_validateIbanReturnsFalse( $ibanToTest ) {
		$bankConverter = $this->factory->newBankDataConverter();

		$this->assertFalse( $bankConverter->validateIban( $ibanToTest ) );
	}

	public function testWhenGivenValidIban_converterReturnsBankData() {
		$bankConverter = $this->factory->newBankDataConverter();

		$bankData = new BankData();
		$bankData->setBankName( 'ING-DiBa' );
		$bankData->setAccount( '0648489890' );
		$bankData->setBankCode( '50010517' );
		$bankData->setBic( 'INGDDEFFXXX' );
		$bankData->setIban( 'DE12500105170648489890' );

		$this->assertEquals( $bankConverter->getBankDataFromIban( 'DE12500105170648489890' ), $bankData );
	}

	public function testWhenGivenValidNonDEIban_converterReturnsIBAN() {
		$bankConverter = $this->factory->newBankDataConverter();

		$bankData = new BankData();
		$bankData->setBankName( '' );
		$bankData->setAccount( '' );
		$bankData->setBankCode( '' );
		$bankData->setBic( '' );
		$bankData->setIban( 'BE68844010370034' );

		$this->assertEquals( $bankConverter->getBankDataFromIban( 'BE68844010370034' ), $bankData );
	}

	public function testWhenGivenValidIban_validateIbanReturnsTrue() {
		$bankConverter = $this->factory->newBankDataConverter();

		$this->assertTrue( $bankConverter->validateIban( 'DE12500105170648489890' ) );
		$this->assertTrue( $bankConverter->validateIban( 'BE68844010370034' ) );
	}

	/**
	 * @dataProvider accountTestProvider
	 */
	public function testWhenGivenInvalidAccountData_converterReturnsFalse( $accountToTest, $bankCodeToTest ) {
		$bankConverter = $this->factory->newBankDataConverter();

		$this->assertFalse( $bankConverter->getBankDataFromAccountData( $accountToTest, $bankCodeToTest ) );
	}

	public function accountTestProvider() {
		return array(
			array( '', '' ),
			array( '0648489890', '' ),
			array( '0648489890', '12310517' ),
			array( '1234567890', '50010517' ),
			array( '', '50010517' ),
		);
	}

	public function testWhenGivenValidAccountData_converterReturnsBankData() {
		$bankConverter = $this->factory->newBankDataConverter();

		$bankData = new BankData();
		$bankData->setBankName( 'ING-DiBa' );
		$bankData->setAccount( '0648489890' );
		$bankData->setBankCode( '50010517' );
		$bankData->setBic( 'INGDDEFFXXX' );
		$bankData->setIban( 'DE12500105170648489890' );

		$this->assertEquals( $bankConverter->getBankDataFromAccountData( '0648489890', '50010517' ), $bankData );
	}
}
