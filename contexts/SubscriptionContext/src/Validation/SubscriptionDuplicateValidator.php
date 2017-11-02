<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\Validation;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepositoryException;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResult;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionDuplicateValidator {

	const SOURCE_NAME = 'subscription_duplicate_subscription';

	private $repository;
	private $cutoffDateTime;

	public function __construct( SubscriptionRepository $repository, \DateTime $cutoffDateTime ) {
		$this->repository = $repository;
		$this->cutoffDateTime = $cutoffDateTime;
	}

	/**
	 * @param Subscription $subscription
	 * @return ValidationResult
	 * @throws SubscriptionRepositoryException
	 */
	public function validate( Subscription $subscription ): ValidationResult {
		$constraintViolations = [];

		if ( $this->repository->countSimilar( $subscription, $this->cutoffDateTime ) > 0 ) {
			$constraintViolations[] = new ConstraintViolation(
				$subscription->getEmail(),
				'The data was already inserted',
				self::SOURCE_NAME
			);
		}

		return new ValidationResult( ...$constraintViolations );
	}

}