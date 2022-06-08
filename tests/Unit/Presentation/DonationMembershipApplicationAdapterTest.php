<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Domain\Model\DonationPayment;
use WMDE\Fundraising\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\Domain\Model\SofortPayment;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter
 */
class DonationMembershipApplicationAdapterTest extends TestCase {

	public function testCompleteDonation_isCorrectlyConvertedToArray(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$donor = $this->getPrivateDonor(
			'Herr', 'Dr.', 'Max', 'Mustermann', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getDirectDebitPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'person',
				'salutation' => 'Herr',
				'title' => 'Dr.',
				'firstName' => 'Max',
				'lastName' => 'Mustermann',
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
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$donor = $this->getCompanyDonor(
			'ACME Inc', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 100000 ), 1, $this->getDirectDebitPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'firma',
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
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$donor = $this->getPrivateDonor(
			'Frau', 'Prof. Dr.', 'Minna', 'Mustermann', 'Demostr. 42',
			'3389', 'Wien', 'AT', 'demo2@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 55000 ), 1, new SofortPayment( 'DXB' ) );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'person',
				'salutation' => 'Frau',
				'title' => 'Prof. Dr.',
				'firstName' => 'Minna',
				'lastName' => 'Mustermann',
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
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$donor = $this->getPrivateDonor(
			'', '', '', '', '',
			'', '', 'AT', 'demo2@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 55000 ), 1, new SofortPayment( 'FFG' ) );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );

		$adapter = new DonationMembershipApplicationAdapter();
		$this->assertEquals(
			[
				'addressType' => 'person',
				'salutation' => '',
				'title' => '',
				'firstName' => '',
				'lastName' => '',
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
	): Donor\PersonDonor {
		return new Donor\PersonDonor(
			new Donor\Name\PersonName( $firstName, $lastName, $salutation, $title ),
			new Donor\Address\PostalAddress( $street, $postCode, $city, $countryCode ),
		$email );
	}

	private function getCompanyDonor( string $companyname, string $street, string $postCode, string $city, string $countryCode, string $email ): Donor\CompanyDonor {
		return new Donor\CompanyDonor(
			new Donor\Name\CompanyName( $companyname ),
			new Donor\Address\PostalAddress( $street, $postCode, $city, $countryCode ),
			$email );
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
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getBankTransferPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, new Donor\AnonymousDonor(), $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );
		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals( [], $adapter->getInitialValidationState( $donation ) );
	}

	public function testDonationWitDonorReturnsValidationStateForPersonalData(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$donor = $this->getPrivateDonor(
			'Herr', 'Dr.', 'Max', 'Mustermann', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getBankTransferPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, $donor, $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );

		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals(
			[ 'address' => true ],
			$adapter->getInitialValidationState( $donation )
		);
	}

	public function testDonationWithDirectDebitAndIbanHasValidBankData(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $this->getDirectDebitPayment() );
		$donation = new Donation( null, Donation::STATUS_NEW, ValidDonation::newDonor(), $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );
		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals(
			[ 'bankData' => true, 'address' => true ],
			$adapter->getInitialValidationState( $donation )
		);
	}

	public function testDonationWithDirectDebitAndMissingIbanHasNoValidBankData(): void {
		$this->markTestIncomplete( "This will need to be updated when updating the donation controllers" );

		$bankData = new BankData();
		$bankData->setIban( new Iban( '' ) );
		$paymentMethod = new DirectDebitPayment( $bankData );
		$payment = new DonationPayment( Euro::newFromCents( 45000 ), 1, $paymentMethod );
		$donation = new Donation( null, Donation::STATUS_NEW, ValidDonation::newDonor(), $payment, false, DonationTrackingInfo::newBlankTrackingInfo(), null );
		$adapter = new DonationMembershipApplicationAdapter();

		$this->assertEquals( [ 'address' => true ], $adapter->getInitialValidationState( $donation ) );
	}
}
