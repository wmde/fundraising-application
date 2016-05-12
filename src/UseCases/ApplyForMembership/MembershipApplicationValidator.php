<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use InvalidArgumentException;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\UseCases\ApplyForMembership\ApplicationValidationResult as Result;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicationValidator {

	/* private */ const MIN_PERSON_YEARLY_PAYMENT_IN_EURO = 24;
	/* private */ const MIN_COMPANY_YEARLY_PAYMENT_IN_EURO = 100;
	/* private */ const MONTHS_PER_YEAR = 12;

	/**
	 * @var ApplyForMembershipRequest
	 */
	private $request;

	/**
	 * @var string[]
	 */
	private $violations;

	public function validate( ApplyForMembershipRequest $applicationRequest ): Result {
		$this->request = $applicationRequest;
		$this->violations = [];

		$this->validateAmount();

		return new Result( $this->violations );
	}

	private function validateAmount() {
		try {
			$amount = Euro::newFromString( $this->request->getPaymentAmountInEuros() );
		}
		catch ( InvalidArgumentException $ex ) {
			$this->addViolation( Result::SOURCE_PAYMENT_AMOUNT, Result::VIOLATION_NOT_MONEY );
			return;
		}

		$this->validateAmountMeetsYearlyMinimum( $amount );
	}

	private function addViolation( string $source, string $type ) {
		$this->violations[$source] = $type;
	}

	private function validateAmountMeetsYearlyMinimum( Euro $amount ) {
		if ( $this->getYearlyPaymentAmount( $amount ) < $this->getYearlyPaymentRequirement() ) {
			$this->addViolation( Result::SOURCE_PAYMENT_AMOUNT, Result::VIOLATION_TOO_LOW );
		}
	}

	private function getYearlyPaymentAmount( Euro $amount ): float {
		return $amount->getEuroFloat() * self::MONTHS_PER_YEAR / $this->request->getPaymentIntervalInMonths();
	}

	private function getYearlyPaymentRequirement(): float {
		return $this->request->isCompanyApplication() ?
			self::MIN_COMPANY_YEARLY_PAYMENT_IN_EURO :
			self::MIN_PERSON_YEARLY_PAYMENT_IN_EURO;
	}

}