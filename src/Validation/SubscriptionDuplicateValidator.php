<?php

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionDuplicateValidator implements InstanceValidator {

	private $repository;
	private $cutoffDateTime;
	private $constraintViolations = [];

	public function __construct( SubscriptionRepository $repository, \DateTime $cutoffDateTime ) {
		$this->repository = $repository;
		$this->cutoffDateTime = $cutoffDateTime;
	}

	public function validate( $instance ): bool {
		$this->constraintViolations = [];
		if ( $this->repository->countSimilar( $instance, $this->cutoffDateTime ) > 0 ) {
			$this->constraintViolations[] = new ConstraintViolation(
				$instance->getEmail(),
				'The data was already inserted',
				Subscription::class . '::duplicate'
			);
		}
		return empty( $this->constraintViolations );
	}

	public function getConstraintViolations(): array {
		return $this->constraintViolations;
	}


}