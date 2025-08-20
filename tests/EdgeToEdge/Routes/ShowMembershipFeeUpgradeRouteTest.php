<?php

declare( strict_types = 1 );

namespace EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\ShowMembershipFeeUpgradeController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FeeChanges;

#[CoversClass( ShowMembershipFeeUpgradeController::class )]
class ShowMembershipFeeUpgradeRouteTest extends WebRouteTestCase {

	private const PATH = '/show-membership-confirmation';
	private const INVALID_TEST_UUID = 'foorchbar';
	private const VALID_TEST_UUID = FeeChanges::UUID_1;

	private const INVALID_TEST_EMAIL = '';
	private const VALID_TEST_EMAIL = FeeChanges::EMAIL;

	public function testNoUUIDInRequest_rendersErrorPageWithCustomMessage(): void {
		//TODO test custom message
		$client = $this->createClient();
		$client->request(
			'GET',
			'show-membership-fee-upgrade',
			[
				'email' => self::VALID_TEST_EMAIL
			]
		);

		$response = $client->getResponse();
		$this->assertFalse();
	}

	public function testInvalidUUIDInRequest_rendersErrorPageWithCustomMessage(): void {
		//TODO test custom message
		$client = $this->createClient();
		$client->request(
			'GET',
			'show-membership-fee-upgrade',
			[
				'uuid' => self::INVALID_TEST_UUID,
				'email' => self::VALID_TEST_EMAIL
			]
		);

		$response = $client->getResponse();
		$this->assertFalse();
	}

	public function testValidUUIDInRequest_rendersFeeUpgradeForm(): void {
		$client = $this->createClient();
		$this->givenStoredFeeChangeInRepository();


		$client->request(
			'GET',
			'show-membership-fee-upgrade',
			[
				'uuid' => self::VALID_TEST_UUID,
				'email' => self::VALID_TEST_EMAIL
			]
		);

		$response = $client->getResponse();

		$this->assertEquals(new Response(), $response);
	}

 private function givenStoredFeeChangeInRepository(): void {
		$feeChange = FeeChanges::newNewFeeChange( FeeChanges::UUID_1 );
	 	$ffFactory = $this->getFactory();
		$feeChangeRepository = $ffFactory->getFeeChangeRepository();
		$feeChangeRepository->storeFeeChange( $feeChange );
 }

}
