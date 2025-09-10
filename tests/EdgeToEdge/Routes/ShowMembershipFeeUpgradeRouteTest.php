<?php

declare( strict_types = 1 );

namespace EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\MembershipFeeUpgradeFrontendFlag;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\MembershipFeeUpgradeHTMLPresenter;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\ShowMembershipFeeUpgradeController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FeeChanges;

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
		$this->assertSame( MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE->value, $dataVars->feeChangeFrontendFlag );
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
		$this->assertSame( MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE->value, $dataVars->feeChangeFrontendFlag );
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
		$this->assertSame( MembershipFeeUpgradeFrontendFlag::SHOW_ERROR_PAGE->value, $dataVars->feeChangeFrontendFlag );
		$this->assertNull( $dataVars->externalMemberId );
		$this->assertSame( '', $dataVars->currentAmountInCents );
		$this->assertSame( '', $dataVars->suggestedAmountInCents );
		$this->assertSame( '', $dataVars->currentInterval );
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

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );

		$this->assertSame( self::VALID_TEST_UUID, $dataVars->uuid );
		$this->assertSame( FeeChanges::EXTERNAL_MEMBER_ID, $dataVars->externalMemberId );
		$this->assertSame( FeeChanges::AMOUNT, $dataVars->currentAmountInCents );
		$this->assertSame( FeeChanges::SUGGESTED_AMOUNT, $dataVars->suggestedAmountInCents );
		$this->assertSame( FeeChanges::INTERVAL, $dataVars->currentInterval );
	}

	public function testValidButAlreadyUsedUUIDInRequest_rendersAlready(): void {
		$client = $this->createClient();
		$this->givenStoredAlreadyChangedFeeChangeInRepository();

		$client->request(
			'GET',
			'change-membership-fee',
			[
				'uuid' => FeeChanges::UUID_2
			]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertSame( MembershipFeeUpgradeFrontendFlag::SHOW_FEE_ALREADY_CHANGED_PAGE->value, $dataVars->feeChangeFrontendFlag );
		$this->assertNull( $dataVars->externalMemberId );
		$this->assertSame( '', $dataVars->currentAmountInCents );
		$this->assertSame( '', $dataVars->suggestedAmountInCents );
		$this->assertSame( '', $dataVars->currentInterval );
	}

	private function givenStoredFeeChangeInRepository(): void {
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

}
