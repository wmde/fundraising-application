<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use Silex\Translator;
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
class DonationConfirmationHtmlPresenterTest extends \PHPUnit_Framework_TestCase {

	public function testWhenPresenterRenders_itPassedParamsToTemplate() {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$translator = $this->getMockBuilder( Translator::class )->disableOriginalConstructor()->getMock();
		$pageSelector = $this->getMockBuilder( SelectedConfirmationPage::class )->disableOriginalConstructor()->getMock();

		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $this->getExpectedRenderParams() );

		$presenter = new DonationConfirmationHtmlPresenter( $twig, $translator );
		$presenter->present(
			ValidDonation::newBookedAnonymousPayPalDonation(),
			'update_token',
			$pageSelector,
			$this->newPiwikEvents()
		);
	}

	private function getExpectedRenderParams(): array {
		return [
			'main_template' => '',
			'templateCampaign' => '',
			'donation' => [
				'id' => null,
				'status' => 'B',
				'amount' => 13.37,
				'interval' => 3,
				'paymentType' => 'PPL',
				'optsIntoNewsletter' => false,
				'bankTransferCode' => '',
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				'cookieDuration' => '',
				'updateToken' => 'update_token'
			],
			'person' => [ ],
			'bankData' => [ ],
			'initialFormValues' => [ ],
			'piwikEvents' => [
				[ 'setCustomVariable', 1, 'Payment', 'some value', PiwikEvents::SCOPE_VISIT ],
				[ 'trackGoal', 4095 ]
			]
		];
	}

	private function newPiwikEvents(): PiwikEvents {
		$piwikEvents = new PiwikEvents();
		$piwikEvents->triggerSetCustomVariable( 1, 'some value', PiwikEvents::SCOPE_VISIT );
		$piwikEvents->triggerTrackGoal( 4095 );

		return $piwikEvents;
	}

}
