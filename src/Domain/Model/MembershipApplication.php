<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplication {

	const ACTIVE_MEMBERSHIP = 'active';
	const SUSTAINING_MEMBERSHIP = 'sustaining';

	/**
	 * @var int|null
	 */
	private $id;

	private $type;
	private $applicant;
	private $payment;

	public function __construct( int $id = null, string $type, MembershipApplicant $applicant, MembershipPayment $payment ) {
		$this->id = $id;
		$this->type = $type;
		$this->applicant = $applicant;
		$this->payment = $payment;
	}

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	public function getApplicant(): MembershipApplicant {
		return $this->applicant;
	}

	public function getPayment(): MembershipPayment {
		return $this->payment;
	}

	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param int $id
	 * @throws \RuntimeException
	 */
	public function setId( int $id ) {
		if ( $this->id !== null && $this->id !== $id ) {
			throw new \RuntimeException( 'Id cannot be changed after initial assignment' );
		}

		$this->id = $id;
	}

}
