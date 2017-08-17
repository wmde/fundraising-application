<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Infrastructure\PiwikEvents;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\SelectedConfirmationPage;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationHtmlPresenterTest extends \PHPUnit\Framework\TestCase {

	private const STATUS_BOOKED = 'status-booked';
	private const STATUS_UNCONFIRMED = 'status-unconfirmed';
	private const MEMBERSHIP_FEE_PAYMENT_DELAY = 42;

	public function testWhenPresenterRenders_itPassedParamsToTemplate(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$pageSelector = $this->getMockBuilder( SelectedConfirmationPage::class )->disableOriginalConstructor()->getMock();

		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $this->getExpectedRenderParams( self::STATUS_BOOKED ) );

		$presenter = new DonationConfirmationHtmlPresenter( $twig );
		$presenter->present(
			ValidDonation::newBookedAnonymousPayPalDonation(),
			'update_token',
			$pageSelector,
			$this->newPiwikEvents(),
			self::MEMBERSHIP_FEE_PAYMENT_DELAY
		);
	}

	private function getExpectedRenderParams( string $mappedStatus ): array {
		return [
			'main_template' => '',
			'templateCampaign' => '',
			'donation' => [
				'id' => null,
				'status' => $mappedStatus,
				'amount' => 13.37,
				'interval' => 3,
				'paymentType' => 'PPL',
				'optsIntoNewsletter' => false,
				'bankTransferCode' => '',
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				'cookieDuration' => '15552000',
				'updateToken' => 'update_token'
			],
			'person' => [ ],
			'bankData' => [ ],
			'initialFormValues' => [ ],
			'piwikEvents' => [
				[ 'setCustomVariable', 1, 'Payment', 'some value', PiwikEvents::SCOPE_VISIT ],
				[ 'trackGoal', 4095 ]
			],
			'delay_in_days' => 42
		];
	}

	private function newPiwikEvents(): PiwikEvents {
		$piwikEvents = new PiwikEvents();
		$piwikEvents->triggerSetCustomVariable( 1, 'some value', PiwikEvents::SCOPE_VISIT );
		$piwikEvents->triggerTrackGoal( 4095 );

		return $piwikEvents;
	}

	public function testWhenPresenterPresents_itPassesMappedStatus(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$pageSelector = $this->getMockBuilder( SelectedConfirmationPage::class )->disableOriginalConstructor()->getMock();

		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $this->getExpectedRenderParams( self::STATUS_UNCONFIRMED ) );

		$presenter = new DonationConfirmationHtmlPresenter( $twig );
		$presenter->present(
			ValidDonation::newIncompleteAnonymousPayPalDonation(),
			'update_token',
			$pageSelector,
			$this->newPiwikEvents(),
			self::MEMBERSHIP_FEE_PAYMENT_DELAY
		);
	}

}
