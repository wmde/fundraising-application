<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentRouteTest extends WebRouteTestCase {

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'add-comment',
			[]
		);
	}

	public function testGivenRequestWithoutParameters_resultIsError() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'add-comment',
			[]
		);

		$response = $client->getResponse();

		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithoutTokens_resultIsError() {
		/**
		 * @var Donation $donation
		 */
		$donation = null;

		$client = $this->createClient( [], function( FunFunFactory $factory ) use ( &$donation ) {
			$donation = $this->storeDonation( $factory->getEntityManager() );
		} );

		$client->request(
			'POST',
			'add-comment',
			[
				'kommentar' => 'Your programmers deserve a raise',
				'public' => '1',
				'eintrag' => 'Uncle Bob',
				'sid' => (string)$donation->getId(),
			]
		);

		$response = $client->getResponse();

		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertErrorJsonResponse( $response );
	}

	private function storeDonation( EntityManager $entityManager ): Donation {
		$donation = new Donation();
		$donation->setAmount( '100' );
		$donation->setDtNew( new DateTime( '1984-01-01' ) );
		$entityManager->persist( $donation );
		$entityManager->flush();
		return $donation;
	}

	public function testGivenRequestWithParameters_resultIsSuccess() {
		/**
		 * @var Donation $donation
		 */
		$donation = null;

		$client = $this->createClient( [], function( FunFunFactory $factory ) use ( &$donation ) {
			$donation = $this->storeDonation( $factory->getEntityManager() );
		} );

		$client->request(
			'POST',
			'add-comment',
			[
				'kommentar' => 'Your programmers deserve a raise',
				'public' => '1',
				'eintrag' => 'Uncle Bob',
				'sid' => (string)$donation->getId(),
				'token' => '1276888%2459b42194b31d0265df452735f6438a234bae2af7',
				'utoken' => 'b5b249c8beefb986faf8d186a3f16e86ef509ab2',
			]
		);

		$response = $client->getResponse();

		$this->assertSuccessJsonResponse( $response );
	}

}
