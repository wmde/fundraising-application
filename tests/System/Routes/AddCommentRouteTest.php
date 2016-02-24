<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Client;
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
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$donation = $this->storeDonation( $factory->getEntityManager() );

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

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

	private function storeDonation( EntityManager $entityManager ): Donation {
		$donation = new Donation();
		$donation->setAmount( '100' );
		$donation->setDtNew( new DateTime( '1984-01-01' ) );
		$entityManager->persist( $donation );
		$entityManager->flush();
		return $donation;
	}

	public function testGivenRequestWithValidParameters_resultIsSuccess() {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$donation = $this->storeDonation( $factory->getEntityManager() );

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

			$this->assertSuccessJsonResponse( $client->getResponse() );
		} );
	}

	public function testGivenRequestWithUnknownDonationId_resultIsError() {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$this->storeDonation( $factory->getEntityManager() );

			$client->request(
				'POST',
				'add-comment',
				[
					'kommentar' => 'Your programmers deserve a raise',
					'public' => '1',
					'eintrag' => 'Uncle Bob',
					'sid' => 25502, // No donation with this id
					'token' => '1276888%2459b42194b31d0265df452735f6438a234bae2af7',
					'utoken' => 'b5b249c8beefb986faf8d186a3f16e86ef509ab2',
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

}
