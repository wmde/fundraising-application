<?php

declare( strict_types = 1 );

namespace EdgeToEdge\Routes;

use PhpParser\Node\Expr\Cast\Object_;
use PHPUnit\Framework\Attributes\CoversClass;
use WMDE\Fundraising\Frontend\App\Controllers\Membership\ShowMembershipFeeUpgradeController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\FeeChanges;

use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes\GetApplicationVarsTrait;

#[CoversClass( ShowMembershipFeeUpgradeController::class )]
class ShowMembershipFeeUpgradeRouteTest extends WebRouteTestCase {

	use GetApplicationVarsTrait;

	private const PATH = '/show-membership-confirmation';
	private const INVALID_TEST_UUID = 'foorchbar';
	private const VALID_TEST_UUID = FeeChanges::UUID_1;

	private const INVALID_TEST_EMAIL = '';
	private const VALID_TEST_EMAIL = FeeChanges::EMAIL;

	public function setUp(): void {
		$this->modifyConfiguration( [ 'skin' => 'laika' ] );
	}

	public function testNoUUIDInRequest_rendersErrorPageWithCustomMessage(): void {
		//TODO test custom message
		$client = $this->createClient();
		$client->request(
			'GET',
			'membership-fee-upgrade',
			[]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertEquals( 'TODO proper error message!!!!!', $dataVars->message );
	}

	public function testInvalidUUIDInRequest_rendersErrorPageWithCustomMessage(): void {
		//TODO test custom message
		$client = $this->createClient();
		$client->request(
			'GET',
			'membership-fee-upgrade',
			[
				'uuid' => self::INVALID_TEST_UUID,
			]
		);

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );
		$this->assertEquals( 'TODO proper error message!!!!!', $dataVars->message );
	}

	public function testValidUUIDInRequest_rendersFeeUpgradeForm(): void {
		$client = $this->createClient();
		$this->givenStoredFeeChangeInRepository();


		$client->request(
			'GET',
			'membership-fee-upgrade',
			[
				'uuid' => self::VALID_TEST_UUID
			]
		);

		$response = $client->getResponse();

		$dataVars = $this->getDataApplicationVars( $client->getCrawler() );

		//TODO maybe test if the dataVars contain the 4 extra fields (url, paymentinfo, uuid,..)

		$this->assertEquals((object)[], $dataVars);
	}

 private function givenStoredFeeChangeInRepository(): void {
		$feeChange = FeeChanges::newNewFeeChange( FeeChanges::UUID_1 );
	 	$ffFactory = $this->getFactory();
		$feeChangeRepository = $ffFactory->getFeeChangeRepository();
		$feeChangeRepository->storeFeeChange( $feeChange );
 }

}
