<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use InvalidArgumentException;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership\ApplicationValidationResult as Result;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class MembershipFeeValidator {

	private const MIN_PERSON_YEARLY_PAYMENT_IN_EURO = 24;
	private const MIN_COMPANY_YEARLY_PAYMENT_IN_EURO = 100;
	private const MONTHS_PER_YEAR = 12;

	const APPLICANT_TYPE_COMPANY = 'firma';
	const APPLICANT_TYPE_PERSON = 'person';

	private $membershipFee;
	private $paymentIntervalInMonths;
	private $applicantType;

	/**
	 * @var string[]
	 */
	private $violations;

	public function validate( string $membershipFee, int $paymentIntervalInMonths, string $applicantType ): Result {
		$this->membershipFee = $membershipFee;
		$this->paymentIntervalInMonths = $paymentIntervalInMonths;
		$this->applicantType = $applicantType;
		$this->violations = [];

		$this->validateAmount();

		return new Result( $this->violations );
	}

	private function validateAmount(): void {
		try {
			$amount = Euro::newFromString( $this->membershipFee );
		}
		catch ( InvalidArgumentException $ex ) {
			$this->addViolation( Result::SOURCE_PAYMENT_AMOUNT, Result::VIOLATION_NOT_MONEY );
			return;
		}

		$this->validateAmountMeetsYearlyMinimum( $amount );
	}

	private function addViolation( string $source, string $type ): void {
		$this->violations[$source] = $type;
	}

	private function validateAmountMeetsYearlyMinimum( Euro $amount ): void {
		if ( $this->getYearlyPaymentAmount( $amount ) < $this->getYearlyPaymentRequirement() ) {
			$this->addViolation( Result::SOURCE_PAYMENT_AMOUNT, Result::VIOLATION_TOO_LOW );
		}
	}

	private function getYearlyPaymentAmount( Euro $amount ): float {
		return $amount->getEuroFloat() * self::MONTHS_PER_YEAR / $this->paymentIntervalInMonths;
	}

	private function getYearlyPaymentRequirement(): float {
		return $this->applicantType === self::APPLICANT_TYPE_COMPANY ?
			self::MIN_COMPANY_YEARLY_PAYMENT_IN_EURO :
			self::MIN_PERSON_YEARLY_PAYMENT_IN_EURO;
	}

}