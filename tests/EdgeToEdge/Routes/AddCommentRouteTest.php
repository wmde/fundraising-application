<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentRouteTest extends WebRouteTestCase {

	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	const NON_EXISTING_DONATION_ID = 25502;

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
		$donation->setCreationTime( new DateTime( '1984-01-01' ) );

		$donationData = $donation->getDataObject();
		$donationData->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
		$donationData->setUpdateTokenExpiry( date( 'Y-m-d H:i:s', time() + 60 * 60 ) );
		$donation->setDataObject( $donationData );

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
					'utoken' => self::CORRECT_UPDATE_TOKEN,
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
					'sid' => self::NON_EXISTING_DONATION_ID,
					'token' => '1276888%2459b42194b31d0265df452735f6438a234bae2af7',
					'utoken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

	public function testGivenRequestWithInvalidUpdateToken_resultIsError() {
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
					'utoken' => 'Not the correct token',
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

}
