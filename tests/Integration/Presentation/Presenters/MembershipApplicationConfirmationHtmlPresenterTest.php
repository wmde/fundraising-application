<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\Presenters\MembershipApplicationConfirmationHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\MembershipContext\Tests\Fixtures\ValidMembershipApplication;

#[CoversClass( MembershipApplicationConfirmationHtmlPresenter::class )]
class MembershipApplicationConfirmationHtmlPresenterTest extends TestCase {

	public function testWhenPresenterPresents_itPassesParametersToTemplate(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( $this->getExpectedRenderParams() );

		$membershipApplication = ValidMembershipApplication::newDomainEntity();

		$presenter = new MembershipApplicationConfirmationHtmlPresenter( $twig );
		$presenter->presentConfirmation(
			$membershipApplication,
			[
				'amount' => 1000,
				'interval' => 3,
				'paymentType' => 'BEZ',
				'iban' => 'I has IBAN',
				'bic' => 'I has BIC',
				'bankname' => 'I has BANK',
			],
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getExpectedRenderParams(): array {
		return [
			'membershipApplication' => [
				'id' => 1,
				'membershipType' => 'sustaining',
				'paymentType' => 'BEZ',
				'status' => 'status-booked',
				'membershipFee' => 10.00,
				'membershipFeeInCents' => 1000,
				'paymentIntervalInMonths' => 3,
				'updateToken' => '',
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
			]
		];
	}

}
