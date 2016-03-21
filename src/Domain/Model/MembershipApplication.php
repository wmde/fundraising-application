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
	 * @var PersonalInfo
	 */
	private $personalInfo;

	/**
	 * @var BankData
	 */
	private $bankData;

	public function __construct( int $id, PersonalInfo $personalInfo, BankData $bankData ) {
		$this->id = $id;
		$this->personalInfo = $personalInfo;
		$this->bankData = $bankData;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return PersonalInfo
	 */
	public function getPersonalInfo() {
		return $this->personalInfo;
	}

	/**
	 * @return BankData
	 */
	public function getBankData() {
		return $this->bankData;
	}

	/**
	 * TODO:
	 * - date of birth & phone
	 * - type, fee interval & fee amount
	 * - account holder
	 */

}
