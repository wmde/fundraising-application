<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddCommentPostRouteTest extends WebRouteTestCase {

	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	const NON_EXISTING_DONATION_ID = 25502;

	public function testGivenRequestWithoutParameters_resultIsError(): void {
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

	public function testGivenRequestWithoutTokens_resultIsError(): void {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ): void {
			$donation = $this->getNewlyStoredDonation( $factory );

			$client->request(
				'POST',
				'add-comment',
				[
					'comment' => 'Your programmers deserve a raise',
					'public' => '1',
					'isAnonymous' => '0',
					'donationId' => (string)$donation->getId(),
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

	private function getNewlyStoredDonation( FunFunFactory $factory ): Donation {
		$factory->setDonationTokenGenerator( new FixedTokenGenerator(
			self::CORRECT_UPDATE_TOKEN,
			new \DateTime( '9001-01-01' )
		) );

		$donation = ValidDonation::newDirectDebitDonation();

		$factory->getDonationRepository()->storeDonation( $donation );

		return $donation;
	}

	public function testGivenRequestWithValidParameters_resultIsSuccess(): void {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ): void {
			$donation = $this->getNewlyStoredDonation( $factory );

			$client->request(
				'POST',
				'add-comment',
				[
					'comment' => 'Your programmers deserve a raise',
					'public' => '1',
					'isAnonymous' => '0',
					'donationId' => (string)$donation->getId(),
					'updateToken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertSuccessJsonResponse( $client->getResponse() );
		} );
	}

	public function testGivenRequestWithUnknownDonationId_resultIsError(): void {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ): void {
			$this->getNewlyStoredDonation( $factory );

			$client->request(
				'POST',
				'add-comment',
				[
					'comment' => 'Your programmers deserve a raise',
					'public' => '1',
					'isAnonymous' => '0',
					'donationId' => self::NON_EXISTING_DONATION_ID,
					'updateToken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

	public function testGivenRequestWithInvalidUpdateToken_resultIsError(): void {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ): void {
			$donation = $this->getNewlyStoredDonation( $factory );

			$client->request(
				'POST',
				'add-comment',
				[
					'comment' => 'Your programmers deserve a raise',
					'public' => '1',
					'isAnonymous' => '0',
					'donationId' => (string)$donation->getId(),
					'updateToken' => 'Not the correct token',
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

}
