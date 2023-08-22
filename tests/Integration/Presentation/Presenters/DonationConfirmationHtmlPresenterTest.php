<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\Tests\Fixtures\DonationUrlAuthenticationLoaderStub;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\DonationConfirmationHtmlPresenter
 *
 * @license GPL-2.0-or-later
 */
class DonationConfirmationHtmlPresenterTest extends TestCase {

	private const UPDATE_TOKEN = 'update_token';
	private const ACCESS_TOKEN = 'access_token';
	private const DONATION_ID = 42;

	public function testWhenPresenterRenders_itPassedParamsToTemplate(): void {
		$expectedParameters = $this->getExpectedRenderParams();

		$presenter = new DonationConfirmationHtmlPresenter(
			$this->newTwigTemplateMock( $expectedParameters ),
			new FakeUrlGenerator(),
			new DonationUrlAuthenticationLoaderStub( [
				'updateToken' => self::UPDATE_TOKEN,
				'accessToken' => self::ACCESS_TOKEN,
			] ),
			[],
			(object)[]
		);

		$donation = ValidDonation::newBookedAnonymousPayPalDonation( self::DONATION_ID );

		$paymentData = [
			'amount' => 1337,
			'interval' => 3,
			'paymentType' => 'PPL',
			'iban' => 'I BAN',
			'bic' => 'I BIC',
			'bankname' => 'I BANK',
		];

		$presenter->present(
			$donation,
			$paymentData,
			$this->newUrls()
		);
	}

	private function getExpectedRenderParams(): array {
		return [
			'donation' => [
				'id' => self::DONATION_ID,
				'amount' => 13.37,
				'amountInCents' => 1337,
				'interval' => 3,
				'paymentType' => 'PPL',
				'newsletter' => false,
				'receipt' => null,
				'bankTransferCode' => '',
				'creationDate' => ( new \DateTime() )->format( 'd.m.Y' ),
				'cookieDuration' => '15552000',
				'updateToken' => self::UPDATE_TOKEN,
				'accessToken' => self::ACCESS_TOKEN,
				'isExported' => false
			],
			'address' => [
				'isAnonymous' => true
			],
			'bankData' => [
				'iban' => 'I BAN',
				'bic' => 'I BIC',
				'bankname' => 'I BANK',
			],
			'urls' => [
				'testUrl' => 'https://example.com/',
				'addComment' => '/such.a.url/AddCommentPage?donationId=42&updateToken=update_token&accessToken=access_token'
			],
			'countries' => [],
			'addressValidationPatterns' => (object)[],
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

	private function newUrls(): array {
		return [
			'testUrl' => 'https://example.com/'
		];
	}

}
