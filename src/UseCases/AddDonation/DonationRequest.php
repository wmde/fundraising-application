<?php

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationRequest {

	# donation
	private $amount = 0.0;
	private $paymentType = '';
	private $interval = 0;

	# address
	private $addressType = '';
	private $salutation = '';
	private $title = '';
	private $companyName = '';
	private $firstName = '';
	private $lastName = '';
	private $postalAddress = '';
	private $postalCode = '';
	private $city = '';
	private $country = '';
	private $emailAddress = '';

	# direct debit related
	private $iban = '';
	private $bic = '';
	private $bankAccount = '';
	private $bankCode = '';
	private $bankName = '';

	# tracking
	private $tracking = ''; # TODO: generated from piwik parameters
	private $source = ''; # TODO: generated from referer
	private $totalImpressionCount = 0;
	private $singleBannerImpressionCount = 0;
	private $color = ''; # TODO: drop this?
	private $skin = ''; # TODO: drop this?
	private $layout = ''; # TODO: drop this?

	# flow control
	private $nextForm = '';
	private $currentForm = '';
	private $lastForm = '';

	public function getAmount(): float {
		return $this->amount;
	}

	public function setAmount( float $amount ) {
		$this->amount = $amount;
	}

	public function getPaymentType(): string {
		return $this->paymentType;
	}

	public function setPaymentType( string $paymentType ) {
		$this->paymentType = $paymentType;
	}

	public function getInterval(): int {
		return $this->interval;
	}

	public function setInterval( int $interval ) {
		$this->interval = $interval;
	}

	public function getAddressType(): string {
		return $this->addressType;
	}

	public function setAddressType( string $addressType ) {
		$this->addressType = $addressType;
	}

	public function getSalutation(): string {
		return $this->salutation;
	}

	public function setSalutation( string $salutation ) {
		$this->salutation = $salutation;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ) {
		$this->title = $title;
	}

	public function getCompanyName(): string {
		return $this->companyName;
	}

	public function setCompanyName( string $companyName ) {
		$this->companyName = $companyName;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ) {
		$this->firstName = $firstName;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ) {
		$this->lastName = $lastName;
	}

	public function getPostalAddress(): string {
		return $this->postalAddress;
	}

	public function setPostalAddress( string $postalAddress ) {
		$this->postalAddress = $postalAddress;
	}

	public function getPostalCode(): string {
		return $this->postalCode;
	}

	public function setPostalCode( string $postalCode ) {
		$this->postalCode = $postalCode;
	}

	public function getCity(): string {
		return $this->city;
	}

	public function setCity( string $city ) {
		$this->city = $city;
	}

	public function getCountry(): string {
		return $this->country;
	}

	public function setCountry( string $country ) {
		$this->country = $country;
	}

	public function getEmailAddress(): string {
		return $this->emailAddress;
	}

	public function setEmailAddress( string $emailAddress ) {
		$this->emailAddress = $emailAddress;
	}

	public function getIban(): string {
		return $this->iban;
	}

	public function setIban( string $iban ) {
		$this->iban = $iban;
	}

	public function getBic(): string {
		return $this->bic;
	}

	public function setBic( string $bic ) {
		$this->bic = $bic;
	}

	public function getBankAccount(): string {
		return $this->bankAccount;
	}

	public function setBankAccount( string $bankAccount ) {
		$this->bankAccount = $bankAccount;
	}

	public function getBankCode(): string {
		return $this->bankCode;
	}

	public function setBankCode( string $bankCode ) {
		$this->bankCode = $bankCode;
	}

	public function getBankName(): string {
		return $this->bankName;
	}

	public function setBankName( string $bankName ) {
		$this->bankName = $bankName;
	}

	public function getTracking(): string {
		return $this->tracking;
	}

	public function setTracking( string $tracking ) {
		$this->tracking = $tracking;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function setSource( string $source ) {
		$this->source = $source;
	}

	public function getTotalImpressionCount(): int {
		return $this->totalImpressionCount;
	}

	public function setTotalImpressionCount( int $totalImpressionCount ) {
		$this->totalImpressionCount = $totalImpressionCount;
	}

	public function getSingleBannerImpressionCount(): int {
		return $this->singleBannerImpressionCount;
	}

	public function setSingleBannerImpressionCount( int $singleBannerImpressionCount ) {
		$this->singleBannerImpressionCount = $singleBannerImpressionCount;
	}

	public function getColor(): string {
		return $this->color;
	}

	public function setColor( string $color ) {
		$this->color = $color;
	}

	public function getSkin(): string {
		return $this->skin;
	}

	public function setSkin( string $skin ) {
		$this->skin = $skin;
	}

	public function getLayout(): string {
		return $this->layout;
	}

	public function setLayout( string $layout ) {
		$this->layout = $layout;
	}

	public function getNextForm(): string {
		return $this->nextForm;
	}

	public function setNextForm( string $nextForm ) {
		$this->nextForm = $nextForm;
	}

	public function getCurrentForm(): string {
		return $this->currentForm;
	}

	public function setCurrentForm( string $currentForm ) {
		$this->currentForm = $currentForm;
	}

	public function getLastForm(): string {
		return $this->lastForm;
	}

	public function setLastForm( string $lastForm ) {
		$this->lastForm = $lastForm;
	}

}