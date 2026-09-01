<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation;

use WMDE\Fundraising\MembershipContext\Domain\Model\MembershipApplication;

class MembershipApplicantDataFormatter {

	/**
	 * @param MembershipApplication $application
	 *
	 * @return array<string, string>
	 */
	public function getAddressArguments( MembershipApplication $application ): array {
		$applicant = $application->getApplicant();
		$name = $applicant->getName();
		$address = $applicant->getPhysicalAddress();

		return [
			'fullName' => $name->getFullName(),
			'salutation' => $name->salutation,
			'title' => $name->title,
			'firstName' => $name->firstName,
			'lastName' => $name->lastName,
			'companyName' => $name->companyName,
			'street' => $address->streetAddress,
			'postcode' => $address->postalCode,
			'city' => $address->city,
			'country' => $address->countryCode,
			'email' => $applicant->getEmailAddress()->getFullAddress(),
			'addressType' => $applicant->isCompany() ? 'firma' : 'person',
		];
	}
}
