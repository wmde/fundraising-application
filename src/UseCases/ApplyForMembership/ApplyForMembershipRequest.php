<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\FreezableValueObject;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipApplicationTrackingInfo;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipRequest {
	use FreezableValueObject;

	private $membershipType;

	private $applicantIsCompany = false;
	private $applicantCompanyName;
	private $applicantSalutation;
	private $applicantTitle;
	private $applicantFirstName;
	private $applicantLastName;

	private $applicantStreetAddress;
	private $applicantPostalCode;
	private $applicantCity;
	private $applicantCountryCode;

	private $applicantEmailAddress;
	private $applicantPhoneNumber;
	private $applicantDateOfBirth;

	private $paymentIntervalInMonths;
	private $paymentAmount;
	private $paymentBankData;

	private $trackingInfo;

	public function getMembershipType(): string {
		return $this->membershipType;
	}

	public function setMembershipType( string $membershipType ) {
		$this->assertIsWritable();
		$this->membershipType = $membershipType;
	}

	/**
	 * @return bool True when the applicant is a company, false when the applicant is a private person
	 */
	public function isCompanyApplication(): bool {
		return $this->applicantIsCompany;
	}

	public function markApplicantAsCompany() {
		$this->assertIsWritable();
		$this->applicantIsCompany = true;
	}

	public function getApplicantCompanyName(): string {
		return $this->applicantCompanyName;
	}

	public function setApplicantCompanyName( string $applicantCompanyName ) {
		$this->applicantCompanyName = $applicantCompanyName;
	}

	public function getApplicantSalutation(): string {
		return $this->applicantSalutation;
	}

	public function setApplicantSalutation( string $applicantSalutation ) {
		$this->assertIsWritable();
		$this->applicantSalutation = $applicantSalutation;
	}

	public function getApplicantTitle(): string {
		return $this->applicantTitle;
	}

	public function setApplicantTitle( string $applicantTitle ) {
		$this->assertIsWritable();
		$this->applicantTitle = $applicantTitle;
	}

	public function getApplicantFirstName(): string {
		return $this->applicantFirstName;
	}

	public function setApplicantFirstName( string $applicantFirstName ) {
		$this->assertIsWritable();
		$this->applicantFirstName = $applicantFirstName;
	}

	public function getApplicantLastName(): string {
		return $this->applicantLastName;
	}

	public function setApplicantLastName( string $applicantLastName ) {
		$this->assertIsWritable();
		$this->applicantLastName = $applicantLastName;
	}

	public function getApplicantStreetAddress(): string {
		return $this->applicantStreetAddress;
	}

	public function setApplicantStreetAddress( string $applicantStreetAddress ) {
		$this->assertIsWritable();
		$this->applicantStreetAddress = $applicantStreetAddress;
	}

	public function getApplicantPostalCode(): string {
		return $this->applicantPostalCode;
	}

	public function setApplicantPostalCode( string $applicantPostalCode ) {
		$this->assertIsWritable();
		$this->applicantPostalCode = $applicantPostalCode;
	}

	public function getApplicantCity(): string {
		return $this->applicantCity;
	}

	public function setApplicantCity( string $applicantCity ) {
		$this->assertIsWritable();
		$this->applicantCity = $applicantCity;
	}

	public function getApplicantCountryCode(): string {
		return $this->applicantCountryCode;
	}

	public function setApplicantCountryCode( string $applicantCountryCode ) {
		$this->assertIsWritable();
		$this->applicantCountryCode = $applicantCountryCode;
	}

	public function getApplicantEmailAddress(): string {
		return $this->applicantEmailAddress;
	}

	public function setApplicantEmailAddress( string $applicantEmailAddress ) {
		$this->assertIsWritable();
		$this->applicantEmailAddress = $applicantEmailAddress;
	}

	public function getApplicantPhoneNumber(): string {
		return $this->applicantPhoneNumber;
	}

	public function setApplicantPhoneNumber( string $applicantPhoneNumber ) {
		$this->assertIsWritable();
		$this->applicantPhoneNumber = $applicantPhoneNumber;
	}

	public function getApplicantDateOfBirth(): string {
		return $this->applicantDateOfBirth;
	}

	public function setApplicantDateOfBirth( string $applicantDateOfBirth ) {
		$this->assertIsWritable();
		$this->applicantDateOfBirth = $applicantDateOfBirth;
	}

	public function getPaymentIntervalInMonths(): int {
		return $this->paymentIntervalInMonths;
	}

	public function setPaymentIntervalInMonths( int $paymentIntervalInMonths ) {
		$this->assertIsWritable();
		$this->paymentIntervalInMonths = $paymentIntervalInMonths;
	}

	public function getPaymentAmountInEuros(): string {
		return $this->paymentAmount;
	}

	public function setPaymentAmountInEuros( string $paymentAmount ) {
		$this->assertIsWritable();
		$this->paymentAmount = $paymentAmount;
	}

	public function getPaymentBankData(): BankData {
		return $this->paymentBankData;
	}

	public function setPaymentBankData( BankData $paymentBankData ) {
		$this->assertIsWritable();
		$this->paymentBankData = $paymentBankData;
	}

	public function getTrackingInfo(): MembershipApplicationTrackingInfo {
		return $this->trackingInfo;
	}

	public function setTrackingInfo( MembershipApplicationTrackingInfo $trackingInfo ) {
		$this->assertIsWritable();
		$this->trackingInfo = $trackingInfo;
	}

}
