<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionDuplicateValidator implements InstanceValidator {

	const SOURCE_NAME = 'subscription_duplicate_subscription';

	private $repository;
	private $cutoffDateTime;
	private $constraintViolations = [];

	public function __construct( SubscriptionRepository $repository, \DateTime $cutoffDateTime ) {
		$this->repository = $repository;
		$this->cutoffDateTime = $cutoffDateTime;
	}

	/**
	 * @param Subscription $subscription
	 *
	 * @return bool
	 */
	public function validate( $subscription ): bool {
		$this->constraintViolations = [];
		if ( $this->repository->countSimilar( $subscription, $this->cutoffDateTime ) > 0 ) {
			$this->constraintViolations[] = new ConstraintViolation(
				$subscription->getEmail(),
				'The data was already inserted',
				self::SOURCE_NAME
			);
		}
		return empty( $this->constraintViolations );
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}


}