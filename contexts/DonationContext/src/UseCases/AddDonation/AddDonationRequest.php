<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddDonation;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationRequest {

	private $donorType;
	private $donorFirstName;
	private $donorLastName;
	private $donorSalutation;
	private $donorTitle;
	private $donorCompany;
	private $donorStreetAddress;
	private $donorPostalCode;
	private $donorCity;
	private $donorCountryCode;
	private $donorEmailAddress;

	private $optIn = '';

	# donation
	private $amount;
	private $paymentType = '';
	private $interval = 0;

	# direct debit related
	private $bankData;

	# tracking
	private $tracking = '';
	private $source = ''; # TODO: generated from referer
	private $totalImpressionCount = 0;
	private $singleBannerImpressionCount = 0;
	// Legacy values, will be deprecated in the future
	private $color = '';
	private $skin = '';
	private $layout = '';

	public function getOptIn(): string {
		return $this->optIn;
	}

	public function setOptIn( string $optIn ) {
		$this->optIn = $optIn;
	}

	public function getAmount(): Euro {
		return $this->amount;
	}

	public function setAmount( Euro $amount ) {
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

	public function getBankData(): ?BankData {
		return $this->bankData;
	}

	public function setBankData( BankData $bankData ) {
		$this->bankData = $bankData;
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

	/*
	 * @deprecated
	 */
	public function getColor(): string {
		return $this->color;
	}

	/*
	 * @deprecated
	 */
	public function getSkin(): string {
		return $this->skin;
	}

	/*
	 * @deprecated
	 */
	public function getLayout(): string {
		return $this->layout;
	}

	public function getDonorType(): string {
		return $this->donorType;
	}

	public function setDonorType( string $donorType ) {
		$this->donorType = $donorType;
	}

	public function getDonorFirstName(): string {
		return $this->donorFirstName;
	}

	public function setDonorFirstName( string $donorFirstName ) {
		$this->donorFirstName = $donorFirstName;
	}

	public function getDonorLastName(): string {
		return $this->donorLastName;
	}

	public function setDonorLastName( string $donorLastName ) {
		$this->donorLastName = $donorLastName;
	}

	public function getDonorSalutation(): string {
		return $this->donorSalutation;
	}

	public function setDonorSalutation( string $donorSalutation ) {
		$this->donorSalutation = $donorSalutation;
	}

	public function getDonorTitle(): string {
		return $this->donorTitle;
	}

	public function setDonorTitle( string $donorTitle ) {
		$this->donorTitle = $donorTitle;
	}

	public function getDonorCompany(): string {
		return $this->donorCompany;
	}

	public function setDonorCompany( string $donorCompany ) {
		$this->donorCompany = $donorCompany;
	}

	public function getDonorStreetAddress(): string {
		return $this->donorStreetAddress;
	}

	public function setDonorStreetAddress( string $donorStreetAddress ) {
		$this->donorStreetAddress = $donorStreetAddress;
	}

	public function getDonorPostalCode(): string {
		return $this->donorPostalCode;
	}

	public function setDonorPostalCode( string $donorPostalCode ) {
		$this->donorPostalCode = $donorPostalCode;
	}

	public function getDonorCity(): string {
		return $this->donorCity;
	}

	public function setDonorCity( string $donorCity ) {
		$this->donorCity = $donorCity;
	}

	public function getDonorCountryCode(): string {
		return $this->donorCountryCode;
	}

	public function setDonorCountryCode( string $donorCountryCode ) {
		$this->donorCountryCode = $donorCountryCode;
	}

	public function getDonorEmailAddress(): string {
		return $this->donorEmailAddress;
	}

	public function setDonorEmailAddress( string $donorEmailAddress ) {
		$this->donorEmailAddress = $donorEmailAddress;
	}

	public function donorIsAnonymous(): bool {
		return $this->getDonorType() === DonorName::PERSON_ANONYMOUS;
	}

	public function donorIsCompany(): bool {
		return $this->getDonorType() === DonorName::PERSON_COMPANY;
	}
}