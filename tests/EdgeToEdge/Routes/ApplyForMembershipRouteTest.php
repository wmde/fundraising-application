<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Data\ValidMembershipApplication;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipRouteTest extends WebRouteTestCase {

	public function testGivenGetRequest_resultHasMethodNotAllowedStatus() {
		$this->assertGetRequestCausesMethodNotAllowedResponse(
			'apply-for-membership',
			[]
		);
	}

	public function testGivenValidRequest_successResponseIsReturned() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$this->assertSame( 'TODO success', $client->getResponse()->getContent() );
		} );
	}

	private function newValidHttpParameters(): array {
		return [
			'membership_type' => ValidMembershipApplication::MEMBERSHIP_TYPE,

			'adresstyp' => ValidMembershipApplication::APPLICANT_TYPE,
			'anrede' => ValidMembershipApplication::APPLICANT_SALUTATION,
			'titel' => ValidMembershipApplication::APPLICANT_TITLE,
			'vorname' => ValidMembershipApplication::APPLICANT_FIRST_NAME,
			'nachname' => ValidMembershipApplication::APPLICANT_LAST_NAME,
			'firma' => '',

			'strasse' => ValidMembershipApplication::APPLICANT_STREET_ADDRESS,
			'postcode' => ValidMembershipApplication::APPLICANT_POSTAL_CODE,
			'ort' => ValidMembershipApplication::APPLICANT_CITY,
			'country' => ValidMembershipApplication::APPLICANT_COUNTRY_CODE,

			'email' => ValidMembershipApplication::APPLICANT_EMAIL_ADDRESS,
			'phone' => ValidMembershipApplication::APPLICANT_PHONE_NUMBER,
			'dob' => ValidMembershipApplication::APPLICANT_DATE_OF_BIRTH,

			'membership_fee_interval' => (string)ValidMembershipApplication::PAYMENT_PERIOD_IN_MONTHS,
			'membership_fee' => (string)ValidMembershipApplication::PAYMENT_AMOUNT_IN_EURO, // TODO: change to localized

			'bank_name' => ValidMembershipApplication::PAYMENT_BANK_NAME,
			'iban' => ValidMembershipApplication::PAYMENT_IBAN,
			'bic' => ValidMembershipApplication::PAYMENT_BIC,
			'account_number' => ValidMembershipApplication::PAYMENT_BANK_ACCOUNT,
			'bank_code' => ValidMembershipApplication::PAYMENT_BANK_CODE,
		];
	}

	public function testGivenRequestWithInsufficientAmount_failureResponseIsReturned() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$httpParameters = $this->newValidHttpParameters();
			$httpParameters['membership_fee'] = '1.00'; // TODO: change to localized

			$client->request(
				'POST',
				'apply-for-membership',
				$httpParameters
			);

			$this->assertSame( 'TODO fail', $client->getResponse()->getContent() );
		} );
	}

	public function testGivenValidRequest_applicationIsPersisted() {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ) {
			$factory->setNullMessenger();

			$client->request(
				'POST',
				'apply-for-membership',
				$this->newValidHttpParameters()
			);

			$application = $factory->getMembershipApplicationRepository()->getApplicationById( 1 );

			$this->assertNotNull( $application );

			$expectedApplication = ValidMembershipApplication::newDomainEntity();
			$expectedApplication->assignId( 1 );

			$this->assertEquals( $expectedApplication, $application );
		} );
	}

}
