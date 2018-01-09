<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\SelectedConfirmationPage;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationHtmlPresenterTest extends \PHPUnit\Framework\TestCase {

	private const STATUS_BOOKED = 'status-booked';
	private const STATUS_UNCONFIRMED = 'status-unconfirmed';

	private const UPDATE_TOKEN = 'update_token';
	private const ACCESS_TOKEN = 'access_token';
	private const DONATION_ID = 42;

	public function testWhenPresenterRenders_itPassedParamsToTemplate(): void {
		$expectedParameters = $this->getExpectedRenderParams();
		$expectedParameters['donation']['status'] = self::STATUS_BOOKED;

		$presenter = new DonationConfirmationHtmlPresenter(
			$this->newTwigTemplateMock( $expectedParameters ),
			new FakeUrlGenerator()
		);

		$donation = ValidDonation::newBookedAnonymousPayPalDonation();
		$donation->assignId( self::DONATION_ID );

		$presenter->present(
			$donation,
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->newSelectedConfirmationPage(),
			$this->newPiwikEvents()
		);
	}

	private function getExpectedRenderParams(): array {
		return [
			'template_name' => '',
			'templateCampaign' => '',
			'donation' => [
				'id' => self::DONATION_ID,
				'amount' => 13.37,
				'interval' => 3,
				'paymentType' => 'PPL',
				'optsIntoNewsletter' => false,
				'bankTransferCode' => '',
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				'cookieDuration' => '15552000',
				'updateToken' => self::UPDATE_TOKEN,
				'accessToken' => self::ACCESS_TOKEN
			],
			'address' => [],
			'bankData' => [],
			'initialFormValues' => [],
			'piwikEvents' => [
				[ 'setCustomVariable', 1, 'Payment', 'some value', PiwikEvents::SCOPE_VISIT ],
				[ 'trackGoal', 4095 ]
			],
			'commentUrl' => 'https://such.a.url/AddCommentPage?donationId=42&updateToken=update_token&accessToken=access_token'
		];
	}

	private function newTwigTemplateMock( array $expectedParameters ): TwigTemplate {
		$twig = $this->createMock( TwigTemplate::class );
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $expectedParameters );
		return $twig;
	}

	private function newSelectedConfirmationPage(): SelectedConfirmationPage {
		return $this->createMock( SelectedConfirmationPage::class );
	}

	private function newPiwikEvents(): PiwikEvents {
		$piwikEvents = new PiwikEvents();
		$piwikEvents->triggerSetCustomVariable( 1, 'some value', PiwikEvents::SCOPE_VISIT );
		$piwikEvents->triggerTrackGoal( 4095 );
		return $piwikEvents;
	}

	public function testWhenPresenterPresents_itPassesMappedStatus(): void {
		$expectedParameters = $this->getExpectedRenderParams();
		$expectedParameters['donation']['status'] = self::STATUS_UNCONFIRMED;

		$presenter = new DonationConfirmationHtmlPresenter(
			$this->newTwigTemplateMock( $expectedParameters ),
			new FakeUrlGenerator()
		);

		$donation = ValidDonation::newIncompleteAnonymousPayPalDonation();
		$donation->assignId( self::DONATION_ID );

		$presenter->present(
			$donation,
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->newSelectedConfirmationPage(),
			$this->newPiwikEvents()
		);
	}

}
