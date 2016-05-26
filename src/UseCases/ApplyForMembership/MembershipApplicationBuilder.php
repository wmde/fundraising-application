<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplicant;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipPayment;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhoneNumber;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationBuilder {

	const COMPANY_APPLICANT_TYPE = 'firma';

	public function newApplicationFromRequest( ApplyForMembershipRequest $request ): MembershipApplication {
		return MembershipApplication::newApplication(
			$request->getMembershipType(),
			$this->newApplicant( $request ),
			$this->newPayment( $request )
		);
	}

	private function newApplicant( ApplyForMembershipRequest $request ): MembershipApplicant {
		return new MembershipApplicant(
			$this->newPersonName( $request ),
			$this->newAddress( $request ),
			new EmailAddress( $request->getApplicantEmailAddress() ),
			new PhoneNumber( $request->getApplicantPhoneNumber() ),
			( $request->getApplicantDateOfBirth() === '' ) ? null : new \DateTime( $request->getApplicantDateOfBirth() )
		);
	}

	private function newPersonName( ApplyForMembershipRequest $request ): PersonName {
		$personName = $this->newBasePersonName( $request );

		$personName->setFirstName( $request->getApplicantFirstName() );
		$personName->setLastName( $request->getApplicantLastName() );
		$personName->setSalutation( $request->getApplicantSalutation() );
		$personName->setTitle( $request->getApplicantTitle() );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newBasePersonName( ApplyForMembershipRequest $request ): PersonName {
		if ( $request->isCompanyApplication() ) {
			$personName = PersonName::newCompanyName();
			$personName->setCompanyName( $request->getApplicantCompanyName() );
			return $personName;
		}

		return PersonName::newPrivatePersonName();
	}

	private function newAddress( ApplyForMembershipRequest $request ): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setCity( $request->getApplicantCity() );
		$address->setCountryCode( $request->getApplicantCountryCode() );
		$address->setPostalCode( $request->getApplicantPostalCode() );
		$address->setStreetAddress( $request->getApplicantStreetAddress() );

		return $address->freeze()->assertNoNullFields();
	}

	private function newPayment( ApplyForMembershipRequest $request ): MembershipPayment {
		return new MembershipPayment(
			$request->getPaymentIntervalInMonths(),
			Euro::newFromString( $request->getPaymentAmountInEuros() ),
			$request->getPaymentBankData()
		);
	}

}
