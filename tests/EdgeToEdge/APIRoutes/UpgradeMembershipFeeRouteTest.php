<?php

declare( strict_types = 1 );

namespace EdgeToEdge\APIRoutes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use WMDE\Fundraising\Frontend\App\Controllers\API\Membership\UpgradeMembershipFeeController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FeeChanges;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;

#[CoversClass( UpgradeMembershipFeeController::class )]
class UpgradeMembershipFeeRouteTest extends WebRouteTestCase {

	private LoggerSpy $loggerSpy;
	private KernelBrowser $client;

	public function setUp(): void {
		$this->client = $this->createClient();

		$this->loggerSpy = new LoggerSpy();
		$this->getFactory()->setLogger( $this->loggerSpy );
	}

	public function testControllerReceivesIncompleteRequestData_ThrowsInternalExceptionDueToParsingErrors(): void {
		$this->client->jsonRequest(
			method:'POST',
			uri:'/api/v1/membership/change-fee',
			parameters: []
		);

		$this->loggerSpy->assertNoLoggingCallsWhereMade();

		$response = $this->client->getResponse();
		$this->assertEquals(
			 [
				 "ERR" => "uuid: This value should be of type string.\nmemberName: This value should be of type string.\namountInEuroCents: This value should be of type int.\npaymentType: This value should be of type string.\n",
				 'validationErrors' => [
					'uuid' => 'This value should be of type string.',
					'memberName' => 'This value should be of type string.',
					'amountInEuroCents' => 'This value should be of type int.',
					'paymentType' => 'This value should be of type string.',
				 ]
			 ],
			 json_decode( $response->getContent() ?: '', true )
		);
		$this->assertEquals( 422, $response->getStatusCode() );
	}

	public function testControllerReceivesInvalidValueFeeChangeData_returnsJSONErrorResponse(): void {
		$this->givenStoredNewFeeChangeInRepository();
		$invalidAmount = -100;

		$this->client->jsonRequest(
			method:'POST',
			uri:'/api/v1/membership/change-fee',
			parameters: [
				'uuid' => FeeChanges::UUID_1,
				'memberName' => FeeChanges::MEMBER_NAME,
				'amountInEuroCents' => $invalidAmount,
				'paymentType' => PaymentType::FeeChange->value,
			]
		);

		$this->assertEquals( 'Fee change failed for UUID. ', $this->loggerSpy->getFirstLogCall()->getMessage() );
		$this->assertEquals(
			[
				'uuid' => '07ddc43d-e184-46b3-b4ad-5550ef0f9450',
				'amount' => -100,
				'paymentType' => 'FCH',
				'validationResult' => [
					'payment' => 'Amount needs to be positive'
				]
			],
			$this->loggerSpy->getFirstLogCall()->getContext()
		);

		$response = $this->client->getResponse();
		$this->assertEquals(
			[
				'status' => 'ERR',
				'errors' => [
					'payment' => 'Amount needs to be positive'
				]
			],
			json_decode( $response->getContent() ?: '', true )
		);
		$this->assertEquals( 200, $response->getStatusCode() );
	}

	public function testControllerReceivesValidChangeableFeeChangeData_returnsJSONStatusOKResponse(): void {
		$this->givenStoredNewFeeChangeInRepository();

		$this->client->jsonRequest(
			method:'POST',
			uri:'/api/v1/membership/change-fee',
			parameters: [
				'uuid' => FeeChanges::UUID_1,
				'memberName' => FeeChanges::MEMBER_NAME,
				'amountInEuroCents' => 5000,
				'paymentType' => PaymentType::FeeChange->value,
			]
		);

		$this->loggerSpy->assertNoLoggingCallsWhereMade();

		$response = $this->client->getResponse();

		$this->assertEquals(
			[
				'status' => 'OK'
			],
			json_decode( $response->getContent() ?: '', true )
		);
		$this->assertEquals( 200, $response->getStatusCode() );
	}

	/**
	 * @return iterable<array{string}>
	 */
	public static function failingUUIDProvider(): iterable {
		yield [ FeeChanges::UUID_2 ];
		yield [ FeeChanges::UUID_3 ];
	}

	#[DataProvider( 'failingUUIDProvider' )]
	public function testControllerReceivesAlreadyChangedFeeChangeData_returnsJSONErrorResponse( string $uuid ): void {
		$this->givenStoredAlreadyChangedFeeChangeInRepository();
		$this->givenStoredExportedFeeChangeInRepository();

		$this->client->jsonRequest(
			method:'POST',
			uri:'/api/v1/membership/change-fee',
			parameters: [
				'uuid' => $uuid,
				'memberName' => FeeChanges::MEMBER_NAME,
				'amountInEuroCents' => 5000,
				'paymentType' => PaymentType::FeeChange->value,
			]
		);

		$this->assertEquals( 'Fee change failed for UUID. ', $this->loggerSpy->getFirstLogCall()->getMessage() );

		$response = $this->client->getResponse();
		$this->assertEquals(
			[
				'status' => 'ERR',
				'errors' => [
					'fee_change_already_submitted' => "This fee change ($uuid) was already submitted"
				]
			],
			json_decode( $response->getContent() ?: '', true )
		);
		$this->assertEquals( 200, $response->getStatusCode() );
	}

	private function givenStoredNewFeeChangeInRepository(): void {
		$feeChange = FeeChanges::newNewFeeChange( FeeChanges::UUID_1 );
		$ffFactory = $this->getFactory();
		$feeChangeRepository = $ffFactory->getFeeChangeRepository();
		$feeChangeRepository->storeFeeChange( $feeChange );
	}

	private function givenStoredAlreadyChangedFeeChangeInRepository(): void {
		$feeChange = FeeChanges::newFilledFeeChange( FeeChanges::UUID_2 );
		$ffFactory = $this->getFactory();
		$feeChangeRepository = $ffFactory->getFeeChangeRepository();
		$feeChangeRepository->storeFeeChange( $feeChange );
	}

	private function givenStoredExportedFeeChangeInRepository(): void {
		$feeChange = FeeChanges::newExportedFeeChange( FeeChanges::UUID_3 );
		$ffFactory = $this->getFactory();
		$feeChangeRepository = $ffFactory->getFeeChangeRepository();
		$feeChangeRepository->storeFeeChange( $feeChange );
	}

}
