<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationValidationResult as Result;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddDonationPolicyValidator {

	private $amountPolicyValidator;
	private $textPolicyValidator;

	public function __construct( AmountPolicyValidator $amountPolicyValidator, TextPolicyValidator $textPolicyValidator ) {
		$this->amountPolicyValidator = $amountPolicyValidator;
		$this->textPolicyValidator = $textPolicyValidator;
	}

	public function needsModeration( AddDonationRequest $request ): bool {
		$violations = array_merge(
			$this->getAmountViolations( $request ),
			$this->getBadWordViolations( $request )
		);

		return !empty( $violations );
	}

	private function getBadWordViolations( AddDonationRequest $request ): array {
		if ( $request->donorIsAnonymous() ) {
			return [];
		}

		return array_merge(
			$this->getPolicyViolationsForField( $request->getDonorFirstName(), Result::SOURCE_DONOR_FIRST_NAME ),
			$this->getPolicyViolationsForField( $request->getDonorLastName(), Result::SOURCE_DONOR_LAST_NAME ),
			$this->getPolicyViolationsForField( $request->getDonorCompany(), Result::SOURCE_DONOR_COMPANY ),
			$this->getPolicyViolationsForField( $request->getDonorStreetAddress(), Result::SOURCE_DONOR_STREET_ADDRESS ),
			$this->getPolicyViolationsForField( $request->getDonorCity(), Result::SOURCE_DONOR_CITY )
		);
	}

	private function getPolicyViolationsForField( string $fieldContent, string $fieldName ): array {
		if ( $fieldContent === '' ) {
			return [];
		}
		if ( $this->textPolicyValidator->textIsHarmless( $fieldContent ) ) {
			return [];
		}
		return [ new ConstraintViolation( $fieldContent, Result::VIOLATION_TEXT_POLICY, $fieldName ) ];
	}

	private function getAmountViolations( AddDonationRequest $request ): array {
		return array_map( function ( ConstraintViolation $violation ) {
				$violation->setSource( Result::SOURCE_PAYMENT_AMOUNT );
				return $violation;
		},
			$this->amountPolicyValidator->validate(
				$request->getAmount()->getEuroFloat(),
				$request->getInterval()
			)->getViolations() );
	}
}