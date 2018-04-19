<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\Tests\Integration\UseCases\GenerateIban;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\PaymentContext\Domain\BankDataConverter;
use WMDE\Fundraising\PaymentContext\Domain\KontoCheckIbanValidator;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\ResponseModel\IbanResponse;
use WMDE\Fundraising\PaymentContext\UseCases\GenerateIban\GenerateIbanRequest;
use WMDE\Fundraising\PaymentContext\UseCases\GenerateIban\GenerateIbanUseCase;

/**
 * @covers \WMDE\Fundraising\PaymentContext\UseCases\GenerateIban\GenerateIbanUseCase
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 *
 * @requires extension konto_check
 */
class GenerateIbanUseCaseTest extends TestCase {

	public function testWhenValidBankAccountDataIsGiven_fullBankDataIsReturned(): void {
		$useCase = $this->newGenerateIbanUseCase();

		$bankData = new BankData();
		$bankData->setBic( 'HASPDEHHXXX' );
		$bankData->setIban( new Iban( 'DE76200505501015754243' ) );
		$bankData->setAccount( '1015754243' );
		$bankData->setBankCode( '20050550' );
		$bankData->setBankName( 'Hamburger Sparkasse' );
		$bankData->freeze()->assertNoNullFields();

		$this->assertEquals(
			IbanResponse::newSuccessResponse( $bankData ),
			$useCase->generateIban( new GenerateIbanRequest( '1015754243', '20050550' ) )
		);
	}

	public function testWhenInvalidBankAccountDataIsGiven_failureResponseIsReturned(): void {
		$useCase = $this->newGenerateIbanUseCase();

		$this->assertEquals(
			IbanResponse::newFailureResponse(),
			$useCase->generateIban( new GenerateIbanRequest( '1015754241', '20050550' ) )
		);
	}

	public function testWhenBlockedBankAccountDataIsGiven_failureResponseIsReturned(): void {
		$useCase = $this->newGenerateIbanUseCase();

		$this->assertEquals(
			IbanResponse::newFailureResponse(),
			$useCase->generateIban( new GenerateIbanRequest( '1194700', '10020500' ) )
		);
	}

	private function newGenerateIbanUseCase(): GenerateIbanUseCase {
		$ibanValidator = new KontoCheckIbanValidator( 'res/blz.lut2f', [ 'DE33100205000001194700' ] );

		return new GenerateIbanUseCase(
			new BankDataConverter( 'res/blz.lut2f', $ibanValidator ),
			$ibanValidator
		);
	}
}
