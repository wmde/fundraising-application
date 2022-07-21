<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\HttpFoundation\Request;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\AddCommentController
 */
class AddCommentPostRouteTest extends WebRouteTestCase {

	private const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	private const NON_EXISTING_DONATION_ID = 25502;
	private const PATH = '/add-comment';

	/**
	 * @todo this test and behavior seems wrong
	 */
	public function testGivenRequestWithoutParameters_resultIsError(): void {
		$client = $this->createClient();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[]
		);

		$response = $client->getResponse();

		$this->assertTrue( $response->isSuccessful(), 'request is successful' );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithoutTokens_resultIsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ( new StoredDonations( $factory ) )->newStoredDirectDebitDonation();

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				[
					'comment' => 'Take my money!',
					'public' => '1',
					'isAnonymous' => '0',
					'donationId' => (string)$donation->getId(),
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

	public function testGivenRequestWithValidParameters_resultIsSuccess(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ( new StoredDonations( $factory ) )->newUpdatableDirectDebitDonation( self::CORRECT_UPDATE_TOKEN );

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				[
					'comment' => 'Take my money!',
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
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$client->request(
				Request::METHOD_POST,
				self::PATH,
				[
					'comment' => 'Take my money!',
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
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ( new StoredDonations( $factory ) )->newUpdatableDirectDebitDonation( self::CORRECT_UPDATE_TOKEN );

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				[
					'comment' => 'Take my money!',
					'public' => '1',
					'isAnonymous' => '0',
					'donationId' => (string)$donation->getId(),
					'updateToken' => 'Not the correct token',
				]
			);

			$this->assertErrorJsonResponse( $client->getResponse() );
		} );
	}

	public function testGivenRequestWithEmoticons_resultIsError(): void {
		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donation = ( new StoredDonations( $factory ) )->newUpdatableDirectDebitDonation( self::CORRECT_UPDATE_TOKEN );

			$client->request(
				Request::METHOD_POST,
				self::PATH,
				[
					'comment' => 'Gotta make dat 💲',
					'public' => '1',
					'isAnonymous' => '0',
					'donationId' => (string)$donation->getId(),
					'updateToken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$response = $client->getResponse();

			$this->assertErrorJsonResponse( $response );
			$this->assertSame( 'comment_failure_text_invalid_chars', $this->getJsonFromResponse( $response )['message'] );
		} );
	}

}
