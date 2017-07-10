<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Doctrine\ORM\EntityManager;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Messenger;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\Fundraising\Store\DonationData;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CancelDonationRouteTest extends WebRouteTestCase {

	const CORRECT_UPDATE_TOKEN = 'b5b249c8beefb986faf8d186a3f16e86ef509ab2';

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
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$client->getCookieJar()->set( new Cookie( 'donation_timestamp', '49152 B.C.' ) );

			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );

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
		} );
	}

	public function testGivenValidUpdateToken_confirmationPageIsShown(): void {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {

			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => (string)$donationId,
					'utoken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertContains( 'Cancellation status: successful', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus(): void {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'/donation/cancel',
			[
				'sid' => '',
				'utoken' => self::CORRECT_UPDATE_TOKEN,
			]
		);
	}

	public function testGivenInvalidUpdateToken_resultIsError(): void {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => (string)$donationId,
					'utoken' => 'Not the correct update token',
				]
			);

			$this->assertContains( 'Cancellation status: failed', $client->getResponse()->getContent() );
		} );
	}

	private function storeDonation( DonationRepository $repo, EntityManager $entityManager ): int {
		$donation = ValidDonation::newDirectDebitDonation();
		$repo->storeDonation( $donation );

		/**
		 * @var DoctrineDonation $doctrineDonation
		 */
		$doctrineDonation = $entityManager->getRepository( DoctrineDonation::class )->find( $donation->getId() );

		$doctrineDonation->modifyDataObject( function( DonationData $data ) {
			$data->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
			$data->setUpdateTokenExpiry( date( 'Y-m-d H:i:s', time() + 60 * 60 ) );
		} );

		$entityManager->persist( $doctrineDonation );
		$entityManager->flush();

		return $donation->getId();
	}

	public function testWhenMailDeliveryFails_noticeIsDisplayed(): void {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );
			$factory->setSuborganizationMessenger( $this->newThrowingMessenger() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => (string)$donationId,
					'utoken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertContains( 'Mail delivery status: failed', $client->getResponse()->getContent() );
		} );
	}

	private function newThrowingMessenger() {
		$failingMessenger = $this->getMockBuilder( Messenger::class )->disableOriginalConstructor()->getMock();
		$failingMessenger->method( 'sendMessageToUser' )->willThrowException( new \RuntimeException() );
		return $failingMessenger;
	}

}
