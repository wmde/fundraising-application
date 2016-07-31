<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class SubscriptionRequest {

	private $salutation = '';
	private $title = '';
	private $firstName = '';
	private $lastName = '';
	private $email = '';
	private $address = '';
	private $postcode = '';
	private $city = '';
	private $wikilogin = false;

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

	public function getEmail(): string {
		return $this->email;
	}

	public function setEmail( string $email ) {
		$this->email = $email;
	}

	public function getAddress(): string {
		return $this->address;
	}

	public function setAddress( string $address ) {
		$this->address = $address;
	}

	public function getPostcode(): string {
		return $this->postcode;
	}

	public function setPostcode( string $postcode ) {
		$this->postcode = $postcode;
	}

	public function getCity(): string {
		return $this->city;
	}

	public function setCity( string $city ) {
		$this->city = $city;
	}

	public function getWikilogin() {
		return $this->wikilogin;
	}

	public function setWikilogin( bool $wikilogin ) {
		$this->wikilogin = $wikilogin;
	}

	/**
	 * Set the wikilogin value from the first value that matches /^(1|0|yes|no)$/
	 *
	 * @param array $values
	 */
	public function setWikiloginFromValues( array $values ) {
		$trueValues = ['yes', '1'];
		$falseValues = ['no', '0'];
		$matchingValues = array_intersect( $values, array_merge( $trueValues, $falseValues ) );
		$wikilogin = in_array( array_shift( $matchingValues ), $trueValues );
		$this->setWikilogin( $wikilogin );
	}

}