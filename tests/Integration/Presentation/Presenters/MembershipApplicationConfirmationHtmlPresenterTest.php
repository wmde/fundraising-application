<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;
use WMDE\Fundraising\MembershipContext\Tests\Data\ValidMembershipApplication;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationConfirmationHtmlPresenter
 */
class MembershipApplicationConfirmationHtmlPresenterTest extends \PHPUnit\Framework\TestCase {

	public function testWhenPresenterPresents_itPassesParametersToTemplate(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $this->getExpectedRenderParams() );

		$membershipApplication = ValidMembershipApplication::newDomainEntity();

		$presenter = new MembershipApplicationConfirmationHtmlPresenter( $twig, new FakeUrlGenerator() );
		$presenter->presentConfirmation(
			$membershipApplication,
			[
				'amount' => 10.00,
				'interval' => 3,
				'paymentType' => 'BEZ',
				'iban' => 'I has IBAN',
				'bic' => 'I has BIC',
				'bankname' => 'I has BANK',
			],
			'update_token'
		);
	}

	private function getExpectedRenderParams(): array {
		return [
			'membershipApplication' => [
				'id' => null,
				'membershipType' => 'sustaining',
				'paymentType' => 'BEZ',
				'status' => 'status-booked',
				'membershipFee' => 10.00,
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
			'bankData' => [
				'iban' => 'I has IBAN',
				'bic' => 'I has BIC',
				'bankname' => 'I has BANK',
			],
			'urls' => [
				'cancelMembership' => '/such.a.url/cancel_membership_application?updateToken=update_token'
			]
		];
	}

}
