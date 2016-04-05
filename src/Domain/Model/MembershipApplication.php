<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplication {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var MembershipApplicant
	 */
	private $applicant;

	/**
	 * @var BankData
	 */
	private $bankData;

	public function __construct( int $id, MembershipApplicant $applicant, BankData $bankData ) {
		$this->id = $id;
		$this->applicant = $applicant;
		$this->bankData = $bankData;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getApplicant(): MembershipApplicant {
		return $this->applicant;
	}

	public function getBankData(): BankData {
		return $this->bankData;
	}

	/**
	 * TODO:
	 * - type, fee interval & fee amount
	 * - account holder
	 */

}
