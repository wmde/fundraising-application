<?php

declare( strict_types = 1 );

namespace EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\MembershipFeeUpgradeFrontendFlag;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\MembershipFeeUpgradeHTMLPresenter;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\ShowMembershipFeeUpgradeController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FeeChanges;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;

#[CoversClass( ShowMembershipFeeUpgradeController::class )]
#[CoversClass( MembershipFeeUpgradeHTMLPresenter::class )]
class ShowMembershipFeeUpgradeRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	private const string INVALID_TEST_UUID = 'foorchbar';
	private const string VALID_TEST_UUID = FeeChanges::UUID_1;

	public function setUp(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
	}

	public function testNoUUIDInRequest_rendersErrorPage(): void {
		$client = $this->createClient();
		$client->request(
			'GET',
			'change-membership-fee',
			[]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertEquals( MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE->value, $dataVars->feeChangeFrontendFlag );
	}

	public function testInvalidUUIDInRequest_rendersErrorPage(): void {
		$client = $this->createClient();
		$client->request(
			'GET',
			'change-membership-fee',
			[
				'uuid' => self::INVALID_TEST_UUID,
			]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertEquals( MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE->value, $dataVars->feeChangeFrontendFlag );
	}

	public function testInvalidUUIDInRequest_errorPageContainsEmptyDataVars(): void {
		$client = $this->createClient();
		$client->request(
			'GET',
			'change-membership-fee',
			[
				'uuid' => self::INVALID_TEST_UUID,
			]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertEquals( MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE->value, $dataVars->feeChangeFrontendFlag );
		$this->assertEquals('', $dataVars->externalMemberId);
		$this->assertEquals('', $dataVars->currentAmountInCents);
		$this->assertEquals('', $dataVars->suggestedAmountInCents);
		$this->assertEquals('', $dataVars->currentInterval);
	}

	public function testValidUUIDInRequest_rendersFeeUpgradeForm(): void {
		$client = $this->createClient();
		$this->givenStoredFeeChangeInRepository();


		$client->request(
			'GET',
			'change-membership-fee',
			[
				'uuid' => self::VALID_TEST_UUID
			]
		);

		$response = $client->getResponse();

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertEquals(self::VALID_TEST_UUID, $dataVars->uuid);
		$this->assertEquals(FeeChanges::EXTERNAL_MEMBER_ID, $dataVars->externalMemberId);
		$this->assertEquals(FeeChanges::AMOUNT, $dataVars->currentAmountInCents);
		$this->assertEquals(FeeChanges::SUGGESTED_AMOUNT, $dataVars->suggestedAmountInCents);
		$this->assertEquals(FeeChanges::INTERVAL, $dataVars->currentInterval);

	}

 private function givenStoredFeeChangeInRepository(): void {
		$feeChange = FeeChanges::newNewFeeChange( FeeChanges::UUID_1 );
	 	$ffFactory = $this->getFactory();
		$feeChangeRepository = $ffFactory->getFeeChangeRepository();
		$feeChangeRepository->storeFeeChange( $feeChange );
 }

}
