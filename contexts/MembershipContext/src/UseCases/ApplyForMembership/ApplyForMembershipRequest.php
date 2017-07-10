<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\FreezableValueObject;
use WMDE\Fundraising\Frontend\MembershipContext\Tracking\MembershipApplicationTrackingInfo;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;

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

	private $paymentType;
	private $paymentIntervalInMonths;
	private $paymentAmount;
	private $bankData;

	private $trackingInfo;
	private $piwikTrackingString;

	public function getMembershipType(): string {
		return $this->membershipType;
	}

	public function setMembershipType( string $membershipType ): void {
		$this->assertIsWritable();
		$this->membershipType = $membershipType;
	}

	/**
	 * @return bool True when the applicant is a company, false when the applicant is a private person
	 */
	public function isCompanyApplication(): bool {
		return $this->applicantIsCompany;
	}

	public function markApplicantAsCompany(): void {
		$this->assertIsWritable();
		$this->applicantIsCompany = true;
	}

	public function getApplicantCompanyName(): string {
		return $this->applicantCompanyName;
	}

	public function setApplicantCompanyName( string $applicantCompanyName ): void {
		$this->applicantCompanyName = $applicantCompanyName;
	}

	public function getApplicantSalutation(): string {
		return $this->applicantSalutation;
	}

	public function setApplicantSalutation( string $applicantSalutation ): void {
		$this->assertIsWritable();
		$this->applicantSalutation = $applicantSalutation;
	}

	public function getApplicantTitle(): string {
		return $this->applicantTitle;
	}

	public function setApplicantTitle( string $applicantTitle ): void {
		$this->assertIsWritable();
		$this->applicantTitle = $applicantTitle;
	}

	public function getApplicantFirstName(): string {
		return $this->applicantFirstName;
	}

	public function setApplicantFirstName( string $applicantFirstName ): void {
		$this->assertIsWritable();
		$this->applicantFirstName = $applicantFirstName;
	}

	public function getApplicantLastName(): string {
		return $this->applicantLastName;
	}

	public function setApplicantLastName( string $applicantLastName ): void {
		$this->assertIsWritable();
		$this->applicantLastName = $applicantLastName;
	}

	public function getApplicantStreetAddress(): string {
		return $this->applicantStreetAddress;
	}

	public function setApplicantStreetAddress( string $applicantStreetAddress ): void {
		$this->assertIsWritable();
		$this->applicantStreetAddress = $applicantStreetAddress;
	}

	public function getApplicantPostalCode(): string {
		return $this->applicantPostalCode;
	}

	public function setApplicantPostalCode( string $applicantPostalCode ): void {
		$this->assertIsWritable();
		$this->applicantPostalCode = $applicantPostalCode;
	}

	public function getApplicantCity(): string {
		return $this->applicantCity;
	}

	public function setApplicantCity( string $applicantCity ): void {
		$this->assertIsWritable();
		$this->applicantCity = $applicantCity;
	}

	public function getApplicantCountryCode(): string {
		return $this->applicantCountryCode;
	}

	public function setApplicantCountryCode( string $applicantCountryCode ): void {
		$this->assertIsWritable();
		$this->applicantCountryCode = $applicantCountryCode;
	}

	public function getApplicantEmailAddress(): string {
		return $this->applicantEmailAddress;
	}

	public function setApplicantEmailAddress( string $applicantEmailAddress ): void {
		$this->assertIsWritable();
		$this->applicantEmailAddress = $applicantEmailAddress;
	}

	public function getApplicantPhoneNumber(): string {
		return $this->applicantPhoneNumber;
	}

	public function setApplicantPhoneNumber( string $applicantPhoneNumber ): void {
		$this->assertIsWritable();
		$this->applicantPhoneNumber = $applicantPhoneNumber;
	}

	public function getApplicantDateOfBirth(): string {
		return $this->applicantDateOfBirth;
	}

	public function setApplicantDateOfBirth( string $applicantDateOfBirth ): void {
		$this->assertIsWritable();
		$this->applicantDateOfBirth = $applicantDateOfBirth;
	}

	public function getPaymentIntervalInMonths(): int {
		return $this->paymentIntervalInMonths;
	}

	public function setPaymentIntervalInMonths( int $paymentIntervalInMonths ): void {
		$this->assertIsWritable();
		$this->paymentIntervalInMonths = $paymentIntervalInMonths;
	}

	public function getPaymentAmountInEuros(): string {
		return $this->paymentAmount;
	}

	public function setPaymentAmountInEuros( string $paymentAmount ): void {
		$this->assertIsWritable();
		$this->paymentAmount = $paymentAmount;
	}

	public function getBankData(): ?BankData {
		return $this->bankData;
	}

	public function setBankData( BankData $bankData ): void {
		$this->assertIsWritable();
		$this->bankData = $bankData;
	}

	public function getTrackingInfo(): MembershipApplicationTrackingInfo {
		return $this->trackingInfo;
	}

	public function setTrackingInfo( MembershipApplicationTrackingInfo $trackingInfo ): void {
		$this->assertIsWritable();
		$this->trackingInfo = $trackingInfo;
	}

	public function getPiwikTrackingString(): string {
		return $this->piwikTrackingString;
	}

	public function setPiwikTrackingString( string $piwikTrackingString ): void {
		$this->assertIsWritable();
		$this->piwikTrackingString = $piwikTrackingString;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ): void {
		$this->assertIsWritable();
		$this->paymentType = $paymentType;
	}

}
