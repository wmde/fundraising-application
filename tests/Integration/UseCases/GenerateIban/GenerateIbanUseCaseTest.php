<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\GenerateIban;

use WMDE\Fundraising\Frontend\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\BankData;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanRequest;
use WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\GenerateIban\GenerateIbanUseCase
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
class GenerateIbanUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		if ( !function_exists( 'lut_init' ) ) {
			$this->markTestSkipped( 'The konto_check needs to be installed!' );
		}
	}

	public function testWhenValidBankAccountDataIsGiven_fullBankDataIsReturned() {
		$useCase = new GenerateIbanUseCase( new BankDataConverter( 'res/blz.lut2f' ) );
		$bankData = new BankData();
		$bankData->setBic( 'HASPDEHHXXX' );
		$bankData->setIban( 'DE76200505501015754243' );
		$bankData->setAccount( '1015754243' );
		$bankData->setBankCode( '20050550' );
		$bankData->setBankName( 'Hamburger Sparkasse' );

		$this->assertEquals(
			$bankData,
			$useCase->generateIban( new GenerateIbanRequest( '1015754243', '20050550' ) )
		);
	}

	public function testWhenInvalidBankAccountDataIsGiven_falseIsReturned() {
		$useCase = new GenerateIbanUseCase( new BankDataConverter( 'res/blz.lut2f' ) );

		$this->assertFalse( $useCase->generateIban( new GenerateIbanRequest( '1015754241', '20050550' ) ) );
	}

}
