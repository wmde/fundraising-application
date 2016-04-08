<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplication {

	/**
	 * @var int|null
	 */
	private $id;

	private $applicant;
	private $payment;

	public function __construct( int $id = null, MembershipApplicant $applicant, MembershipPayment $payment ) {
		$this->id = $id;
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

	/**
	 * @param int $id
	 * @throws \RuntimeException
	 */
	public function setId( int $id ) {
		if ( $this->id !== null ) {
			throw new \RuntimeException( 'Can only set an id when it is not yet assigned' );
		}

		$this->id = $id;
	}

}
