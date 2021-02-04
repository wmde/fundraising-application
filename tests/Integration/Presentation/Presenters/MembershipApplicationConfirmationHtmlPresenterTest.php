<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;
use WMDE\Fundraising\MembershipContext\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationConfirmationHtmlPresenter
 *
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipApplicationConfirmationHtmlPresenterTest extends \PHPUnit\Framework\TestCase {

	private const STATUS_BOOKED = 'status-booked';
	private const STATUS_UNCONFIRMED = 'status-unconfirmed';

	/** @dataProvider applicationStatusProvider */
	public function testWhenPresenterPresents_itPassesParametersToTemplate( bool $isConfirmed, string $expectedMappedStatus ): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $this->getExpectedRenderParams( $expectedMappedStatus ) );

		$bankDataGeneratorStub = $this->getMockBuilder( BankDataGenerator::class )->getMock();
		$bankDataGeneratorStub->expects( $this->never() )->method( 'getBankDataFromIban' );

		$membershipApplication = ValidMembershipApplication::newDomainEntityUsingPayPal();
		if ( $isConfirmed === true ) {
			$membershipApplication->confirm();
		}

		$presenter = new MembershipApplicationConfirmationHtmlPresenter( $twig, $bankDataGeneratorStub, new FakeUrlGenerator() );
		$presenter->presentConfirmation(
			$membershipApplication,
			'update_token'
		);
	}

	public function applicationStatusProvider(): array {
		return [
			[ true, self::STATUS_BOOKED ],
			[ false, self::STATUS_UNCONFIRMED ]
		];
	}

	private function getExpectedRenderParams( string $mappedStatus ): array {
		return [
			'membershipApplication' => [
				'id' => null,
				'membershipType' => 'sustaining',
				'paymentType' => 'PPL',
				'status' => $mappedStatus,
				'membershipFee' => '10.00',
				'paymentIntervalInMonths' => 3,
				'updateToken' => 'update_token',
				'incentives' => []
			],
			'address' => [
				'salutation' => 'Herr',
				'title' => '',
				'fullName' => 'Potato The Great',
				'streetAddress' => 'Nyan street',
				'postalCode' => '1234',
				'city' => 'Berlin',
				'email' => 'jeroendedauw@gmail.com',
				'countryCode' => 'DE',
				'applicantType' => 'person'
			],
			'bankData' => [],
			'payPalData' => [
				'firstPaymentDate' => '01.02.2021'
			],
			'urls' => [
				'cancelMembership' => '/such.a.url/cancel_membership_application?updateToken=update_token'
			]
		];
	}

}
