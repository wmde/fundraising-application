<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationRouteTest extends WebRouteTestCase {

	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

	public function testGivenValidArguments_requestResultsIn200() {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/donation/cancel',
			[
				'sid' => '',
				'token' => '',
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
	}

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'/donation/cancel',
			[
				'sid' => '',
				'token' => '',
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);
	}

	public function testGivenInvalidUpdateToken_resultIsError() {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => (string)$donationId,
					'token' => '',
					'utoken' => 'Not the correct update token',
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

	// TODO: refactor once token generation is done by the repo
	private function storeDonation( DonationRepository $repo, EntityManager $entityManager ): int {
		$donation = ValidDonation::newDonation();
		$repo->storeDonation( $donation );

		/**
		 * @var DoctrineDonation $doctrineDonation
		 */
		$doctrineDonation = $entityManager->getRepository( DoctrineDonation::class )->find( $donation->getId() );

		$doctrineDonation->encodeAndSetData( array_merge(
			$doctrineDonation->getDecodedData(),
			[ 'utoken' => self::CORRECT_UPDATE_TOKEN ]
		) );

		$entityManager->persist( $doctrineDonation );
		$entityManager->flush();

		return $donation->getId();
	}

}
