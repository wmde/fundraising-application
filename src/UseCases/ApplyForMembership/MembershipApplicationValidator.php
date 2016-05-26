<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult as Result;
use WMDE\Fundraising\Frontend\Validation\MembershipFeeValidator;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationValidator {

	private $feeValidator;

	/**
	 * @var ApplyForMembershipRequest
	 */
	private $request;

	/**
	 * @var string[]
	 */
	private $violations;

	public function __construct( MembershipFeeValidator $feeValidator ) {
		$this->feeValidator = $feeValidator;
	}

	public function validate( ApplyForMembershipRequest $applicationRequest ): Result {
		$this->request = $applicationRequest;
		$this->violations = [];

		$result = new Result(
			$this->feeValidator->validate(
				$applicationRequest->getPaymentAmountInEuros(),
				$applicationRequest->getPaymentIntervalInMonths(),
				$applicationRequest->isCompanyApplication() ?
					MembershipFeeValidator::APPLICANT_TYPE_COMPANY : MembershipFeeValidator::APPLICANT_TYPE_PERSON
			)->getViolations()
		);

		return $result;
	}

}