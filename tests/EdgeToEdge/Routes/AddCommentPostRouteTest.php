<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\App\Controllers\Donation\AddCommentController;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;

#[CoversClass( AddCommentController::class )]
class AddCommentPostRouteTest extends WebRouteTestCase {

	private const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';
	private const NON_EXISTING_DONATION_ID = 25502;
	private const PATH = '/add-comment';

	public function testGivenRequestWithoutParameters_resultIsError(): void {
		/** @var KernelBrowser $client */
		$client = $this->createClient();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[]
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_FORBIDDEN, $response->getStatusCode() );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithoutTokens_resultIsError(): void {
		$client = $this->createClient();
		$donation = $this->storeDirectDebitDonation();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[
				'comment' => 'Take my money!',
				'isPublic' => '1',
				'withName' => '1',
				'donationId' => (string)$donation->getId(),
			]
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_FORBIDDEN, $response->getStatusCode() );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithValidParameters_resultIsSuccess(): void {
		$client = $this->createClient();
		$donation = $this->storeDirectDebitDonation();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[
				'comment' => 'Take my money!',
				'isPublic' => '1',
				'withName' => '1',
				'donationId' => (string)$donation->getId(),
				'updateToken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
		$this->assertSuccessJsonResponse( $response );
	}

	public function testGivenRequestWithUnknownDonationId_resultIsError(): void {
		$client = $this->createClient();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[
				'comment' => 'Take my money!',
				'isPublic' => '1',
				'withName' => '1',
				'donationId' => self::NON_EXISTING_DONATION_ID,
				'updateToken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_FORBIDDEN, $response->getStatusCode() );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithInvalidUpdateToken_resultIsError(): void {
		$client = $this->createClient();
		$donation = $this->storeDirectDebitDonation();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[
				'comment' => 'Take my money!',
				'isPublic' => '1',
				'withName' => '1',
				'donationId' => (string)$donation->getId(),
				'updateToken' => 'Not the correct token',
			]
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_FORBIDDEN, $response->getStatusCode() );
		$this->assertErrorJsonResponse( $response );
	}

	public function testGivenRequestWithEmoticons_resultIsError(): void {
		$client = $this->createClient();
		$donation = $this->storeDirectDebitDonation();

		$client->request(
			Request::METHOD_POST,
			self::PATH,
			[
				'comment' => 'Gotta make dat 💲',
				'isPublic' => '1',
				'withName' => '1',
				'donationId' => (string)$donation->getId(),
				'updateToken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$response = $client->getResponse();
		$this->assertSame( Response::HTTP_OK, $response->getStatusCode() );
		$this->assertErrorJsonResponse( $response );
		$this->assertSame( 'comment_failure_text_invalid_chars', $this->getJsonFromResponse( $response )['message'] );
	}

	private function storeDirectDebitDonation(): Donation {
		return ( new StoredDonations( $this->getFactory() ) )->newUpdatableDirectDebitDonation( self::CORRECT_UPDATE_TOKEN );
	}

}
