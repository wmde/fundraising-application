<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter
 *
 * @license GNU GPL v2+
 */
class DonationConfirmationHtmlPresenterTest extends TestCase {

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
			new FakeUrlGenerator(),
			[],
			(object) []
		);

		$donation = ValidDonation::newBookedAnonymousPayPalDonation();
		$donation->assignId( self::DONATION_ID );

		$presenter->present(
			$donation,
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->newUrls()
		);
	}

	private function getExpectedRenderParams(): array {
		return [
			'donation' => [
				'id' => self::DONATION_ID,
				'amount' => 13.37,
				'interval' => 3,
				'paymentType' => 'PPL',
				'optsIntoNewsletter' => false,
				'optsIntoDonationReceipt' => null,
				'bankTransferCode' => '',
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				'cookieDuration' => '15552000',
				'updateToken' => self::UPDATE_TOKEN,
				'accessToken' => self::ACCESS_TOKEN
			],
			'address' => [
				'isAnonymous' => true
			],
			'bankData' => [],
			'urls' => [
				'testUrl' => 'https://example.com/',
				'addComment' => '/such.a.url/AddCommentPage?donationId=42&updateToken=update_token&accessToken=access_token'
			],
			'countries' => [],
			'addressValidationPatterns' => (object) [],
			'addressType' => 'anonym'

		];
	}

	private function newTwigTemplateMock( array $expectedParameters ): TwigTemplate {
		/** @var TwigTemplate&MockObject $twig */
		$twig = $this->createMock( TwigTemplate::class );
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $expectedParameters );
		return $twig;
	}

	public function testWhenPresenterPresents_itPassesMappedStatus(): void {
		$expectedParameters = $this->getExpectedRenderParams();
		$expectedParameters['donation']['status'] = self::STATUS_UNCONFIRMED;

		$presenter = new DonationConfirmationHtmlPresenter(
			$this->newTwigTemplateMock( $expectedParameters ),
			new FakeUrlGenerator(),
			[],
			(object) []
		);

		$donation = ValidDonation::newIncompleteAnonymousPayPalDonation();
		$donation->assignId( self::DONATION_ID );

		$presenter->present(
			$donation,
			self::UPDATE_TOKEN,
			self::ACCESS_TOKEN,
			$this->newUrls()
		);
	}

	private function newUrls(): array {
		return [
			'testUrl' => 'https://example.com/'
		];
	}

}
