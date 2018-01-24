<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\MembershipContext\UseCases\ApplyForMembership;

use WMDE\EmailAddress\EmailAddress;
use WMDE\Euro\Euro;
use WMDE\Fundraising\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantAddress;
use WMDE\Fundraising\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\MembershipContext\Domain\Model\Payment;
use WMDE\Fundraising\MembershipContext\Domain\Model\PhoneNumber;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\PaymentContext\Domain\Model\PayPalPayment;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationBuilder {

	const COMPANY_APPLICANT_TYPE = 'firma';

	public function newApplicationFromRequest( ApplyForMembershipRequest $request ): Application {
		return Application::newApplication(
			$request->getMembershipType(),
			$this->newApplicant( $request ),
			$this->newPayment( $request ),
			$request->getOptsIntoDonationReceipt()
		);
	}

	private function newApplicant( ApplyForMembershipRequest $request ): Applicant {
		return new Applicant(
			$this->newPersonName( $request ),
			$this->newAddress( $request ),
			new EmailAddress( $request->getApplicantEmailAddress() ),
			new PhoneNumber( $request->getApplicantPhoneNumber() ),
			( $request->getApplicantDateOfBirth() === '' ) ? null : new \DateTime( $request->getApplicantDateOfBirth() )
		);
	}

	private function newPersonName( ApplyForMembershipRequest $request ): ApplicantName {
		if ( $request->isCompanyApplication() ) {
			return $this->newCompanyPersonName( $request );
		} else {
			return $this->newPrivatePersonName( $request );
		}
	}

	private function newPrivatePersonName( ApplyForMembershipRequest $request ): ApplicantName {
		$personName = ApplicantName::newPrivatePersonName();
		$personName->setFirstName( $request->getApplicantFirstName() );
		$personName->setLastName( $request->getApplicantLastName() );
		$personName->setSalutation( $request->getApplicantSalutation() );
		$personName->setTitle( $request->getApplicantTitle() );
		return $personName->freeze()->assertNoNullFields();
	}

	private function newCompanyPersonName( ApplyForMembershipRequest $request ): ApplicantName {
		$personName = ApplicantName::newCompanyName();
		$personName->setCompanyName( $request->getApplicantCompanyName() );
		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress( ApplyForMembershipRequest $request ): ApplicantAddress {
		$address = new ApplicantAddress();

		$address->setCity( $request->getApplicantCity() );
		$address->setCountryCode( $request->getApplicantCountryCode() );
		$address->setPostalCode( $request->getApplicantPostalCode() );
		$address->setStreetAddress( $request->getApplicantStreetAddress() );

		return $address->freeze()->assertNoNullFields();
	}

	private function newPayment( ApplyForMembershipRequest $request ): Payment {
		return new Payment(
			$request->getPaymentIntervalInMonths(),
			Euro::newFromString( $request->getPaymentAmountInEuros() ),
			$this->newPaymentMethod( $request )
		);
	}

	private function newPaymentMethod( ApplyForMembershipRequest $request ): PaymentMethod {
		if ( $request->getPaymentType() === PaymentMethod::DIRECT_DEBIT ) {
			return new DirectDebitPayment( $request->getBankData() );
		}

		if ( $request->getPaymentType() === PaymentMethod::PAYPAL ) {
			return new PayPalPayment( new PayPalData() );
		}

		throw new \RuntimeException( 'Unsupported payment type' );
	}

}
