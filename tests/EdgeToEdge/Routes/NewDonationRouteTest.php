<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use WMDE\Fundraising\Frontend\App\Controllers\Donation\NewDonationController;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

#[CoversClass( NewDonationController::class )]
#[CoversClass( DonationFormPresenter::class )]
class NewDonationRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	public function testWhenFormParametersArePassedInRequest_theyArePassedToTheTemplate(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'GET',
			'/',
			[
				'amount' => '1234',
				'paymentType' => 'UEB',
				'interval' => 6,
				'addressType' => 'person'
			]
		);
		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( 1234, $applicationVars->initialFormValues->amount );
		$this->assertSame( 'UEB', $applicationVars->initialFormValues->paymentType );
		$this->assertSame( 6, $applicationVars->initialFormValues->paymentIntervalInMonths );
		$this->assertSame( 'person', $applicationVars->initialFormValues->addressType );
	}

	/**
	 * @param array<string, mixed> $validPaymentInput
	 * @param array<string, mixed> $expected
	 */
	#[DataProvider( 'paymentInputProvider' )]
	public function testGivenPaymentInput_paymentDataIsInitiallyValidated( array $validPaymentInput, array $expected ): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'POST',
			'/donation/new',
			$validPaymentInput
		);
		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		// This is the "legacy" check, paymentErrorFields is the field that contains more detailed validation data
		$this->assertSame( $expected['validity'], $applicationVars->validationResult->paymentData );
		$this->assertSame( $expected['paymentErrorFields'], $applicationVars->validationResult->paymentErrorFields );
		$this->assertSame( $expected['amount'], $applicationVars->initialFormValues->amount );
		// This assertion and its expectation data can be removed when https://phabricator.wikimedia.org/T351827
		// (removing `isCustomAmount` from server data) is resolved
		$this->assertSame( $expected['isCustomAmount'], $applicationVars->initialFormValues->isCustomAmount );
	}

	/**
	 * @return iterable<string, array{
	 *     0: array{amount: string, paymentType: string, interval: string|int},
	 *     1: array{validity: bool, amount: int, isCustomAmount: bool, paymentErrorFields: string[]}
	 *	 }>
	 */
	public static function paymentInputProvider(): iterable {
		yield 'valid one-time direct debit' => [
			[
				'amount' => '10000',
				'paymentType' => 'BEZ',
				'interval' => '0',
			],
			[
				'validity' => true,
				'amount' => 10000,
				'isCustomAmount' => false,
				'paymentErrorFields' => []
			]
		];
		yield 'valid recurring paypal' => [
			[
				'amount' => '12345',
				'paymentType' => 'PPL',
				'interval' => 6
			],
			[
				'validity' => true,
				'amount' => 12345,
				'isCustomAmount' => true,
				'paymentErrorFields' => []
			]
		];
		yield 'valid one-time credit card' => [
			[
				'amount' => '870',
				'paymentType' => 'MCP',
				'interval' => '0'
			],
			[
				'validity' => true,
				'amount' => 870,
				'isCustomAmount' => true,
				'paymentErrorFields' => []
			]
		];
		yield 'valid one-time custom amount' =>	[
			[
				'amount' => '870',
				'paymentType' => 'BEZ',
				'interval' => '0'
			],
			[
				'validity' => true,
				'amount' => 870,
				'isCustomAmount' => true,
				'paymentErrorFields' => []
			]
		];
		yield 'invalid amount' => [
			[
				'amount' => '0',
				'paymentType' => 'PPL',
				'interval' => 6
			],
			[
				'validity' => false,
				'amount' => 0,
				'isCustomAmount' => false,
				'paymentErrorFields' => [ 'amount' ],
			]
		];
		yield 'negative amount (should be set to 0)' => [
			[
				'amount' => '-9999',
				'paymentType' => 'PPL',
				'interval' => 6
			],
			[
				'validity' => false,
				'amount' => 0,
				'isCustomAmount' => false,
				'paymentErrorFields' => [ 'amount' ],
			]
		];
		yield 'invalid payment type' => [
			[
				'amount' => '10000',
				'paymentType' => 'BTC',
				'interval' => 6
			],
			[
				'validity' => false,
				'amount' => 10000,
				'isCustomAmount' => false,
				'paymentErrorFields' => [ 'paymentType' ],
			]
		];
	}

	public function testWhenPassingTrackingData_itGetsPassedToThePresenter(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'POST',
			'/donation/new',
			[
				'impCount' => 12,
				'bImpCount' => 3
			]
		);
		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( 12, $applicationVars->tracking->impressionCount );
		$this->assertSame( 3, $applicationVars->tracking->bannerImpressionCount );
	}

	#[DataProvider( 'receiptInputProvider' )]
	public function testPassesReceiptToPresenter( string $receipt, ?bool $expected ): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
		$client = $this->createClient();

		$client->request(
			'GET',
			'/',
			[ 'receipt' => $receipt ]
		);
		$applicationVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( $expected, $applicationVars->initialFormValues->receipt );
	}

	/**
	 * @return iterable<string,array{string,?bool}>
	 */
	public static function receiptInputProvider(): iterable {
		yield 'receipt true' => [ 'true', true ];
		yield 'receipt false' => [ 'false', false ];
		yield 'receipt null' => [ 'null', null ];
		yield 'receipt is any other string' => [ 'I am not a receipt', null ];
	}
}
