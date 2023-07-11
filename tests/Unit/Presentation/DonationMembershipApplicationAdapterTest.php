<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Euro\Euro;
use WMDE\Fundraising\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter;
use WMDE\Fundraising\PaymentContext\Domain\BankDataGenerator;
use WMDE\Fundraising\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\PaymentContext\Domain\Model\ExtendedBankData;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\Domain\Model\Payment;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentInterval;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentReferenceCode;
use WMDE\Fundraising\PaymentContext\Domain\Model\SofortPayment;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\DonationMembershipApplicationAdapter
 */
class DonationMembershipApplicationAdapterTest extends TestCase {

	private const IBAN = 'DE49123455679923567800';
	private const BIC = 'COBADE4711';
	private const BANKNAME = 'Co Dir';

	public function testCompleteDonation_isCorrectlyConvertedToArray(): void {
		$donor = $this->givenPrivateDonor(
			'Herr', 'Dr.', 'Max', 'Mustermann', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = $this->givenDirectDebitPayment();
		$donation = $this->givenDonation( $donor );

		$adapter = $this->givenAdapter();
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
				'iban' => self::IBAN,
				'bic' => self::BIC,
				'bankname' => self::BANKNAME,
			],
			$adapter->getInitialMembershipFormValues( $donation, $payment )
		);
	}

	public function testCompleteCompanyDonation_isCorrectlyConvertedToArray(): void {
		$donor = $this->getCompanyDonor(
			'ACME Inc', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = $this->givenDirectDebitPayment();
		$donation = $this->givenDonation( $donor );

		$adapter = $this->givenAdapter();
		$this->assertEquals(
			[
				'addressType' => 'firma',
				'companyName' => 'ACME Inc',
				'street' => 'Demostr. 42',
				'postcode' => '08771',
				'city' => 'Bärlin',
				'country' => 'DE',
				'email' => 'demo@cat.goat',
				'iban' => self::IBAN,
				'bic' => self::BIC,
				'bankname' => self::BANKNAME,
			],
			$adapter->getInitialMembershipFormValues( $donation, $payment )
		);
	}

	public function testDonationWithNonDebitPayment_isCorrectlyConvertedToArray(): void {
		$donor = $this->givenPrivateDonor(
			'Frau', 'Prof. Dr.', 'Minna', 'Mustermann', 'Demostr. 42',
			'3389', 'Wien', 'AT', 'demo2@cat.goat'
		);
		$payment = $this->givenSofortPayment();
		$donation = $this->givenDonation( $donor );

		$adapter = $this->givenAdapter();
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
			$adapter->getInitialMembershipFormValues( $donation, $payment )
		);
	}

	public function testDonationWithEmptiedSensitiveFields_isCorrectlyConvertedToArray(): void {
		$donor = $this->givenPrivateDonor(
			'', '', '', '', '',
			'', '', 'AT', 'demo2@cat.goat'
		);
		$payment = $this->givenSofortPayment();
		$donation = $this->givenDonation( $donor );

		$adapter = $this->givenAdapter();
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
			$adapter->getInitialMembershipFormValues( $donation, $payment )
		);
	}

	private function givenPrivateDonor( string $salutation, string $title, string $firstName, string $lastName,
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

	public function testDefaultValidationStateIsEmpty(): void {
		$payment = $this->givenBankTransferPayment();
		$donation = $this->givenDonation( new Donor\AnonymousDonor() );
		$adapter = $this->givenAdapter();

		$this->assertEquals( [], $adapter->getInitialValidationState( $donation, $payment ) );
	}

	public function testDonationWitDonorReturnsValidationStateForPersonalData(): void {
		$donor = $this->givenPrivateDonor(
			'Herr', 'Dr.', 'Max', 'Mustermann', 'Demostr. 42',
			'08771', 'Bärlin', 'DE', 'demo@cat.goat'
		);
		$payment = $this->givenBankTransferPayment();
		$donation = $this->givenDonation( $donor );
		$adapter = $this->givenAdapter();

		$this->assertEquals(
			[ 'address' => true ],
			$adapter->getInitialValidationState( $donation, $payment )
		);
	}

	public function testDonationWithDirectDebitAndIbanHasValidBankData(): void {
		$payment = $this->givenDirectDebitPayment();
		$donation = $this->givenDonation( ValidDonation::newDonor() );
		$adapter = $this->givenAdapter();

		$this->assertEquals(
			[ 'bankData' => true, 'address' => true ],
			$adapter->getInitialValidationState( $donation, $payment )
		);
	}

	public function testDonationWithDirectDebitAndMissingIbanHasNoValidBankData(): void {
		$payment = DirectDebitPayment::create(
			1,
			Euro::newFromCents( 1000 ),
			PaymentInterval::OneTime,
			new Iban( '' ),
			self::BIC
		);

		$donation = $this->givenDonation( ValidDonation::newDonor() );
		$adapter = $this->givenAdapter();

		$this->assertEquals( [ 'address' => true ], $adapter->getInitialValidationState( $donation, $payment ) );
	}

	private function givenBankDataGeneratorForBankName( string $bankname ): MockObject|BankDataGenerator {
		$bankDataGenerator = $this->createMock( BankDataGenerator::class );
		$bankDataGenerator->method( 'getBankDataFromIban' )->willReturn(
			new ExtendedBankData(
				new Iban( self::IBAN ),
				self::BIC,
				'any account',
				'any code',
				$bankname
			)
		);

		return $bankDataGenerator;
	}

	private function givenDirectDebitPayment(): Payment {
		return DirectDebitPayment::create(
			1,
			Euro::newFromCents( 1000 ),
			PaymentInterval::OneTime,
			new Iban( self::IBAN ),
			self::BIC
		);
	}

	private function givenSofortPayment(): Payment {
		return SofortPayment::create(
			1,
			Euro::newFromCents( 1000 ),
			PaymentInterval::OneTime,
			new PaymentReferenceCode( 'AA', 'AAAAAA', 'A' )
		);
	}

	private function givenBankTransferPayment(): Payment {
		return BankTransferPayment::create(
			1,
			Euro::newFromCents( 1000 ),
			PaymentInterval::OneTime,
			new PaymentReferenceCode( 'AA', 'AAAAAA', 'A' )
		);
	}

	private function givenDonation( Donor $donor ): Donation {
		return new Donation(
			1,
			$donor,
			1,
			DonationTrackingInfo::newBlankTrackingInfo(),
			null
		);
	}

	private function givenAdapter(): DonationMembershipApplicationAdapter {
		return new DonationMembershipApplicationAdapter( $this->givenBankDataGeneratorForBankName( self::BANKNAME ) );
	}
}
