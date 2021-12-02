<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\App\EventHandlers\TrackBannerDonationRedirects;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FixedTokenGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\TrackBannerDonationRedirects
 */
class TrackBannerDonationRedirectsTest extends WebRouteTestCase {

	private const CORRECT_ACCESS_TOKEN = 'KindlyAllowMeAccess';
	private const ADD_ROUTE = 'add_donation';
	private const CONFIRMATION_ROUTE = 'show_donation_confirmation';
	private const DISALLOWED_ROUTE = 'show-donation-form';
	private const URL_PARAMETER = 'banner_submission';
	private const TEST_CAMPAIGN = 'test_campaign';
	private const TEST_KEYWORD = 'test_keyword';

	private SessionInterface $session;
	private KernelBrowser $client;
	private Donation $donation;

	public function setUp(): void {
		$this->session = new Session( new MockArraySessionStorage() );

		/** @var KernelBrowser $client */
		$client = $this->createClient();
		$this->client = $client;
		self::getContainer()->set(
			TrackBannerDonationRedirects::class,
			new TrackBannerDonationRedirects(
				$this->session,
				self::ADD_ROUTE,
				self::CONFIRMATION_ROUTE,
				self::URL_PARAMETER
			)
		);
	}

	public function testWhenRedirectingToPaymentProcessor_withUrlParameter_tracksRequest(): void {
		$this->whenPostingDataToRoute(
			self::ADD_ROUTE,
			[
				TrackBannerDonationRedirects::PIWIK_CAMPAIGN => self::TEST_CAMPAIGN,
				TrackBannerDonationRedirects::PIWIK_KWD => self::TEST_KEYWORD,
				'banner_submission' => 1
			]
		);

		$this->verifySessionItemsExist();
	}

	public function testWhenRedirectingToPaymentProcessor_withoutUrlParameter_doesNotTrackRequest(): void {
		$this->whenPostingDataToRoute(
			self::ADD_ROUTE,
			[
				TrackBannerDonationRedirects::PIWIK_CAMPAIGN => self::TEST_CAMPAIGN,
				TrackBannerDonationRedirects::PIWIK_KWD => self::TEST_KEYWORD
			]
		);

		$this->verifySessionItemsWereRemoved();
	}

	public function testWhenNotOnAddRoute_doesNotTrackRequest(): void {
		$this->whenPostingDataToRoute(
			self::DISALLOWED_ROUTE,
			[
				TrackBannerDonationRedirects::PIWIK_CAMPAIGN => self::TEST_CAMPAIGN,
				TrackBannerDonationRedirects::PIWIK_KWD => self::TEST_KEYWORD,
				'banner_submission' => 1
			]
		);

		$this->verifySessionItemsWereRemoved();
	}

	public function testWhenNotOnAddRoute_andSessionItemsExist_removesSessionItems(): void {
		$this->whenTrackingItemsAreInSession();
		$this->whenRequestingRoute( self::DISALLOWED_ROUTE );

		$this->verifySessionItemsWereRemoved();
	}

	public function testWhenOnConfirmationRouteWithSessionItems_addsTrackingItemsToQuery(): void {
		$this->makeStoredDonation();
		$this->whenTrackingItemsAreInSession();

		$response = $this->whenRequestingRoute(
			self::CONFIRMATION_ROUTE,
			[
				'id' => $this->donation->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN
			]
		);

		$location = $response->headers->get( 'Location', '' );
		$campaignParameter = '&' . TrackBannerDonationRedirects::PIWIK_CAMPAIGN . '=' . self::TEST_CAMPAIGN;
		$keywordParameter = '&' . TrackBannerDonationRedirects::PIWIK_KWD . '=' . self::TEST_KEYWORD;

		$this->assertTrue( $response->isRedirection(), 'URL should redirect' );
		$this->assertStringContainsString( $campaignParameter, $location );
		$this->assertStringContainsString( $keywordParameter, $location );
	}

	public function testWhenOnConfirmationRouteWithSessionItems_andQueryItemsAreInUrl_doesNotRedirect(): void {
		$this->makeStoredDonation();
		$this->whenTrackingItemsAreInSession();

		$response = $this->whenRequestingRoute(
			self::CONFIRMATION_ROUTE,
			[
				'id' => $this->donation->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN,
				TrackBannerDonationRedirects::PIWIK_CAMPAIGN => self::TEST_CAMPAIGN,
				TrackBannerDonationRedirects::PIWIK_KWD, self::TEST_KEYWORD
			]
		);

		$this->assertFalse( $response->isRedirection(), 'URL should not redirect' );
	}

	public function testWhenOnConfirmationRouteWithQueryItems_removesSessionItems(): void {
		$this->makeStoredDonation();
		$this->whenTrackingItemsAreInSession();

		$this->whenRequestingRoute(
			self::CONFIRMATION_ROUTE,
			[
				'id' => $this->donation->getId(),
				'accessToken' => self::CORRECT_ACCESS_TOKEN,
				TrackBannerDonationRedirects::PIWIK_CAMPAIGN => self::TEST_CAMPAIGN,
				TrackBannerDonationRedirects::PIWIK_KWD, self::TEST_KEYWORD
			]
		);

		$this->verifySessionItemsWereRemoved();
	}

	private function whenPostingDataToRoute( string $route, array $data ): void {
		$this->client->request(
			'post',
			self::newUrlForNamedRoute( $route ),
			$data
		);
	}

	private function whenRequestingRoute( string $route, array $parameters = [] ): Response {
		$this->client->request( 'get', self::newUrlForNamedRoute( $route ), $parameters );
		return $this->client->getResponse();
	}

	private function whenTrackingItemsAreInSession(): void {
		$this->session->set( TrackBannerDonationRedirects::PIWIK_CAMPAIGN, self::TEST_CAMPAIGN );
		$this->session->set( TrackBannerDonationRedirects::PIWIK_KWD, self::TEST_KEYWORD );
	}

	private function verifySessionItemsExist(): void {
		$this->assertSame( self::TEST_CAMPAIGN, $this->session->get( TrackBannerDonationRedirects::PIWIK_CAMPAIGN ) );
		$this->assertSame( self::TEST_KEYWORD, $this->session->get( TrackBannerDonationRedirects::PIWIK_KWD ) );
	}

	private function verifySessionItemsWereRemoved(): void {
		$this->assertNull( $this->session->get( TrackBannerDonationRedirects::PIWIK_CAMPAIGN ) );
		$this->assertNull( $this->session->get( TrackBannerDonationRedirects::PIWIK_KWD ) );
	}

	private function makeStoredDonation(): void {
		$factory = $this->getFactory();

		$factory->setDonationTokenGenerator(
			new FixedTokenGenerator(
				self::CORRECT_ACCESS_TOKEN
			)
		);

		$donation = ValidDonation::newDirectDebitDonation();

		$factory->getDonationRepository()->storeDonation( $donation );

		$this->donation = $donation;
	}

	private static function newUrlForNamedRoute( string $routeName ): string {
		return self::getContainer()->get( 'router' )->generate(
			$routeName,
			[],
			UrlGeneratorInterface::RELATIVE_PATH
		);
	}
}
