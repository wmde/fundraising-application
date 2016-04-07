<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationRequest {

	/**
	 * @var PersonalInfo|null
	 */
	private $personalInfo;
	private $optIn = '';

	# donation
	private $amount;
	private $paymentType = '';
	private $interval = 0;

	# direct debit related
	private $iban = '';
	private $bic = '';
	private $bankAccount = '';
	private $bankCode = '';
	private $bankName = '';

	# tracking
	private $tracking = '';
	private $source = ''; # TODO: generated from referer
	private $totalImpressionCount = 0;
	private $singleBannerImpressionCount = 0;
	private $color = ''; # TODO: drop this?
	private $skin = ''; # TODO: drop this?
	private $layout = ''; # TODO: drop this?

	/**
	 * @return PersonalInfo|null
	 */
	public function getPersonalInfo() {
		return $this->personalInfo;
	}

	public function setPersonalInfo( PersonalInfo $personalInfo = null ) {
		$this->personalInfo = $personalInfo;
	}

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

	public function getIban(): string {
		return $this->iban;
	}

	public function setIban( string  $iban ) {
		$this->iban = $iban;
	}

	public function getBic(): string {
		return $this->bic;
	}

	public function setBic( string  $bic ) {
		$this->bic = $bic;
	}

	public function getBankAccount(): string {
		return $this->bankAccount;
	}

	public function setBankAccount( string  $bankAccount ) {
		$this->bankAccount = $bankAccount;
	}

	public function getBankCode(): string {
		return $this->bankCode;
	}

	public function setBankCode( string  $bankCode ) {
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

	public static function getPreferredValue( array $values ) {
		foreach ( $values as $value ) {
			if ( $value !== null && $value !== '' ) {
				return $value;
			}
		}

		return '';
	}

	public static function concatTrackingFromVarCouple( string $campaign, string $keyword ): string {
		if ( $campaign !== '' ) {
			return strtolower( implode( '/', array_filter( [ $campaign, $keyword ] ) ) );
		}

		return '';
	}

}