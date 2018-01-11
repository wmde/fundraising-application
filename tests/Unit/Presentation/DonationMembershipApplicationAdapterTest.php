<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;
use WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter;
use PHPUnit\Framework\TestCase;

class DonationMembershipApplicationAdapterTest extends TestCase {

	public function testCompleteDonation_isCorrectlyConvertedToArray(): void {
		$donor = $this->getPrivateDonor(
			'Herr', 'Dr.', 'Max', 'Mustermann', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getDirectDebitPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, new DonationTrackingInfo, null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'person',
				'salutation' => 'Herr',
				'title' => 'Dr.',
				'firstName' => 'Max',
				'lastName' => 'Mustermann',
				'companyName' => '',
				'street' => 'Demostr. 42',
				'postcode' => '08771',
				'city' => 'Bärlin',
				'country' => 'DE',
				'email' => 'demo@cat.goat',
				'iban' => 'DE49123455679923567800',
				'bic' => 'COBADE4711',
				'accountNumber' => '9923 5678 00',
				'bankCode' => '1234 5567',
				'bankname' => 'Co Dir',
			],
			$adapter->getInitialMembershipFormValues( $donation )
		);
	}

	public function testCompleteCompanyDonation_isCorrectlyConvertedToArray(): void {
		$donor = $this->getCompanyDonor(
			'ACME Inc', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 100000 ), 1, $this->getDirectDebitPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, new DonationTrackingInfo, null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'firma',
				'salutation' => '',
				'title' => '',
				'firstName' => '',
				'lastName' => '',
				'companyName' => 'ACME Inc',
				'street' => 'Demostr. 42',
				'postcode' => '08771',
				'city' => 'Bärlin',
				'country' => 'DE',
				'email' => 'demo@cat.goat',
				'iban' => 'DE49123455679923567800',
				'bic' => 'COBADE4711',
				'accountNumber' => '9923 5678 00',
				'bankCode' => '1234 5567',
				'bankname' => 'Co Dir',
			],
			$adapter->getInitialMembershipFormValues( $donation )
		);
	}

	public function testDonationWithNonDebitPayment_isCorrectlyConvertedToArray(): void {
		$donor = $this->getPrivateDonor(
			'Frau', 'Prof. Dr.', 'Minna', 'Mustermann', 'Demostr. 42',
			'3389', 'Wien', 'AT', 'demo2@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 55000 ), 1, new SofortPayment( 'DXB' ) );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, new DonationTrackingInfo, null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'person',
				'salutation' => 'Frau',
				'title' => 'Prof. Dr.',
				'firstName' => 'Minna',
				'lastName' => 'Mustermann',
				'companyName' => '',
				'street' => 'Demostr. 42',
				'postcode' => '3389',
				'city' => 'Wien',
				'country' => 'AT',
				'email' => 'demo2@cat.goat'
			],
			$adapter->getInitialMembershipFormValues( $donation )
		);
	}

	public function testDonationWithEmptiedSensitiveFields_isCorrectlyConvertedToArray(): void {
		$donor = $this->getPrivateDonor(
			'', '', '', '', '',
			'', '', 'AT', 'demo2@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 55000 ), 1, new SofortPayment( 'FFG' ) );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, new DonationTrackingInfo, null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'person',
				'salutation' => '',
				'title' => '',
				'firstName' => '',
				'lastName' => '',
				'companyName' => '',
				'street' => '',
				'postcode' => '',
				'city' => '',
				'country' => 'AT',
				'email' => 'demo2@cat.goat'
			],
			$adapter->getInitialMembershipFormValues( $donation )
		);
	}

	private function getPrivateDonor( string $salutation, string $title, string $firstName, string $lastName,
		string $street, string $postCode, string $city, string $countryCode, string $email
	): Donor {
		$donorName = DonorName::newPrivatePersonName();
		$donorName->setSalutation( $salutation );
		$donorName->setTitle( $title );
		$donorName->setFirstName( $firstName );
		$donorName->setLastName( $lastName );

		$donorAddress = new DonorAddress();
		$donorAddress->setStreetAddress( $street );
		$donorAddress->setPostalCode( $postCode );
		$donorAddress->setCity( $city );
		$donorAddress->setCountryCode( $countryCode );

		return new Donor( $donorName, $donorAddress, $email );
	}

	private function getCompanyDonor( string $companyname, string $street, string $postCode, string $city, string $countryCode, string $email ): Donor {
		$donorName = DonorName::newCompanyName();
		$donorName->setCompanyName( $companyname );

		$donorAddress = new DonorAddress();
		$donorAddress->setStreetAddress( $street );
		$donorAddress->setPostalCode( $postCode );
		$donorAddress->setCity( $city );
		$donorAddress->setCountryCode( $countryCode );

		return new Donor( $donorName, $donorAddress, $email );
	}

	private function getDirectDebitPayment(): DirectDebitPayment {
		$bankData = new BankData();
		$bankData->setBankName( 'Co Dir' );
		$bankData->setAccount( '9923 5678 00' );
		$bankData->setBankCode( '1234 5567' );
		$bankData->setIban( new Iban( 'DE49123455679923567800' ) );
		$bankData->setBic( 'COBADE4711' );
		return new DirectDebitPayment( $bankData );
	}

	private function getBankTransferPayment(): BankTransferPayment {
		return new BankTransferPayment( 'WQXXXX' );
	}

	public function testDefaultValidationStateIsEmpty(): void {

		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getBankTransferPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, null, $payment, false, new DonationTrackingInfo, null );
		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals( [], $adapter->getInitialValidationState( $donation ) );
	}

	public function testDonationWitDonorReturnsValidationStateForPersonalData(): void {
		$donor = $this->getPrivateDonor(
			'Herr', 'Dr.', 'Max', 'Mustermann', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getBankTransferPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, new DonationTrackingInfo, null );

		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals(
			['address' => true],
			$adapter->getInitialValidationState( $donation )
		);
	}

	public function testDonationWithDirectDebitAndIbanHasValidBankData(): void {

		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getDirectDebitPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, null, $payment, false, new DonationTrackingInfo, null );
		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals(
			['bankData' => true],
			$adapter->getInitialValidationState( $donation )
		);
	}

	public function testDonationWithDirectDebitAndMissingIbanHasNoValidBankData(): void {
		$bankData = new BankData();
		$bankData->setIban( new Iban( '' ) );
		$paymentMethod = new DirectDebitPayment( $bankData );
		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $paymentMethod );
		$donation = new Donation( null, Donation::STATUS_NEW, null, $payment, false, new DonationTrackingInfo, null );
		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals( [], $adapter->getInitialValidationState( $donation ) );
	}
}
