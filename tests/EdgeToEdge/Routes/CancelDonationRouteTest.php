<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation as DoctrineDonation;
use WMDE\Fundraising\DonationContext\DataAccess\DonationData;
use WMDE\Fundraising\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\Messenger;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

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
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
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
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => (string)$donationId,
					'utoken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertStringContainsString( 'Cancellation status: successful', $client->getResponse()->getContent() );
		} );
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
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => (string)$donationId,
					'utoken' => 'Not the correct update token',
				]
			);

			$this->assertStringContainsString( 'Cancellation status: failed', $client->getResponse()->getContent() );
		} );
	}

	private function storeDonation( DonationRepository $repo, EntityManager $entityManager ): int {
		$donation = ValidDonation::newDirectDebitDonation();
		$repo->storeDonation( $donation );

		/**
		 * @var DoctrineDonation $doctrineDonation
		 */
		$doctrineDonation = $entityManager->getRepository( DoctrineDonation::class )->find( $donation->getId() );

		$doctrineDonation->modifyDataObject( static function ( DonationData $data ): void {
			$data->setUpdateToken( self::CORRECT_UPDATE_TOKEN );
			$data->setUpdateTokenExpiry( date( 'Y-m-d H:i:s', time() + 60 * 60 ) );
		} );

		$entityManager->persist( $doctrineDonation );
		$entityManager->flush();

		return $donation->getId();
	}

	public function testWhenMailDeliveryFails_noticeIsDisplayed(): void {
		$this->markTestIncomplete( "This should work again when we finish updating the donation controllers" );

		$this->createEnvironment( function ( Client $client, FunFunFactory $factory ): void {
			$donationId = $this->storeDonation( $factory->getDonationRepository(), $factory->getEntityManager() );
			$factory->setSubOrganizationMessenger( $this->newThrowingMessenger() );

			$client->request(
				'POST',
				'/donation/cancel',
				[
					'sid' => (string)$donationId,
					'utoken' => self::CORRECT_UPDATE_TOKEN,
				]
			);

			$this->assertStringContainsString( 'Mail delivery status: failed', $client->getResponse()->getContent() );
		} );
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
