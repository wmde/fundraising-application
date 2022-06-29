<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\Messenger;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Frontend\Tests\Fixtures\StoredDonations;

/**
 * @covers \WMDE\Fundraising\Frontend\App\Controllers\Donation\CancelDonationController
 */
class CancelDonationRouteTest extends WebRouteTestCase {

	private const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

	public function testGivenValidArguments_requestResultsIn200(): void {
		$client = $this->createClient();

		$client->request(
			'POST',
			'/donation/cancel',
			[
				'sid' => '',
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$this->assertSame( 200, $client->getResponse()->getStatusCode() );
	}

	public function testCancellationIsSuccessful_cookieIsCleared(): void {
		$client = $this->createClient();
		$client->getCookieJar()->set( new Cookie( 'donation_timestamp', '49152 B.C.' ) );

		$donationId = $this->storeDonation();

		$client->request(
			'POST',
			'/donation/cancel',
			[
				'sid' => (string)$donationId,
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		/** @var Cookie $cookie */
		$cookie = $client->getResponse()->headers->getCookies()[0];
		$this->assertSame( 'donation_timestamp', $cookie->getName() );
		$this->assertNull( $cookie->getValue() );
	}

	public function testGivenValidUpdateToken_confirmationPageIsShown(): void {
		$client = $this->createClient();
		$donationId = $this->storeDonation();

		$client->request(
			'POST',
			'/donation/cancel',
			[
				'sid' => (string)$donationId,
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$this->assertStringContainsString( 'Cancellation status: successful', $client->getResponse()->getContent() );
	}

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus(): void {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/donation/cancel',
			[
				'sid' => '',
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$this->assertSame( Response::HTTP_METHOD_NOT_ALLOWED, $client->getResponse()->getStatusCode() );
	}

	public function testGivenInvalidUpdateToken_resultIsError(): void {
		$client = $this->createClient();
		$donationId = $this->storeDonation();

		$client->request(
			'POST',
			'/donation/cancel',
			[
				'sid' => (string)$donationId,
				'utoken' => 'Not the correct update token',
			]
		);

		$this->assertStringContainsString( 'Cancellation status: failed', $client->getResponse()->getContent() );
	}

	private function storeDonation(): int {
		$donation = ( new StoredDonations( $this->getFactory() ) )->newDeletableDirectDebitDonation( self::CORRECT_UPDATE_TOKEN );

		return $donation->getId();
	}

	public function testWhenMailDeliveryFails_noticeIsDisplayed(): void {
		$client = $this->createClient();
		$this->getFactory()->setSubOrganizationMessenger( $this->newThrowingMessenger() );
		$donationId = $this->storeDonation();

		$client->request(
			'POST',
			'/donation/cancel',
			[
				'sid' => (string)$donationId,
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);

		$this->assertStringContainsString( 'Mail delivery status: failed', $client->getResponse()->getContent() );
	}

	/**
	 * @return Messenger&MockObject
	 */
	private function newThrowingMessenger(): Messenger {
		$failingMessenger = $this->createMock( Messenger::class );
		$failingMessenger->method( 'sendMessageToUser' )->willThrowException( new \RuntimeException() );
		return $failingMessenger;
	}

}
