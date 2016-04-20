<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Tests\Integration\Validation;

use WMDE\Fundraising\Frontend\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentWithoutAssociatedData;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\AllowedValuesValidator;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\AmountValidator;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;
use WMDE\Fundraising\Frontend\Validation\PersonalInfoValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\DonationValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationValidatorTest extends ValidatorTestCase {

	/** @var DonationValidator */
	private $donationValidator;

	public function setUp() {
		$this->donationValidator = $this->newDonationValidator();
	}

	public function testGivenValidDonation_validationIsSuccessful() {
		$donation = $this->newDonation();

		$this->assertEmpty( $this->donationValidator->validate( $donation )->getViolations() );
	}

	public function testNoPersonalInfoGiven_validatorReturnsTrue() {
		$donation = new Donation(
			null,
			Donation::STATUS_NEW,
			Donation::NO_APPLICANT,
			$this->newDonationPayment(),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo()
		);

		$this->assertTrue( $this->donationValidator->validate( $donation )->isSuccessful() );
	}

	private function newDonationPayment( float $amount = 13.37 ): DonationPayment {
		return new DonationPayment(
			Euro::newFromFloat( $amount ),
			3,
			new BankTransferPayment( 'transfer code' )
		);
	}

	public function testTooHighAmountGiven_needsModerationReturnsTrue() {
		$donation = new Donation(
			null,
			Donation::STATUS_NEW,
			Donation::NO_APPLICANT,
			$this->newDonationPayment( 35000 ),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo()
		);

		$this->assertTrue( $this->donationValidator->needsModeration( $donation ) );
	}

	public function testPersonalInfoValidationFails_validatorReturnsFalse() {
		$donation = ValidDonation::newDirectDebitDonation();

		$personalInfoValidator = $this->getMockBuilder( PersonalInfoValidator::class )->disableOriginalConstructor()->getMock();
		$personalInfoValidator->method( 'validate' )
			->willReturn( new ValidationResult( new ConstraintViolation( '', 'Name missing', 'company' ) ) );

		$donationValidator = new DonationValidator(
			new AmountValidator( 1 ),
			new AmountPolicyValidator( 1000, 200, 300 ),
			$personalInfoValidator,
			new TextPolicyValidator(),
			new AllowedValuesValidator( [ PaymentType::DIRECT_DEBIT ] ),
			$this->newBankDataValidator()
		);

		$this->assertFalse( $donationValidator->validate( $donation )->isSuccessful() );

		$this->assertConstraintWasViolated( $donationValidator->validate( $donation ), 'company' );
	}

	public function testGivenBadWords_needsModerationReturnsTrue() {
		$textPolicyValidator = $this->getMock( TextPolicyValidator::class );
		$textPolicyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );

		$donationValidator = new DonationValidator(
			new AmountValidator( 1 ),
			new AmountPolicyValidator( 1000, 200, 300 ),
			$this->newMockPersonalInfoValidator(),
			$textPolicyValidator,
			new AllowedValuesValidator( [ PaymentType::DIRECT_DEBIT ] ),
			$this->newBankDataValidator()
		);

		$this->assertTrue( $donationValidator->needsModeration( $this->newDonation() ) );
	}

	public function testNoPaymentTypeGiven_validatorReturnsFalse() {
		$donation = $this->newDonationWithPaymentMethod( new PaymentWithoutAssociatedData( '' ) );

		$this->assertFalse( $this->donationValidator->validate( $donation )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $donation ),
			'zahlweise'
		);
	}

	private function newDonationWithPaymentMethod( PaymentMethod $paymentMethod ): Donation {
		return new Donation(
			null,
			Donation::STATUS_NEW,
			ValidDonation::newDonor(),
			new DonationPayment(
				Euro::newFromFloat( 13.37 ),
				3,
				$paymentMethod
			),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo()
		);
	}

	public function testUnsupportedPaymentTypeGiven_validatorReturnsFalse() {
		$donation = $this->newDonationWithPaymentMethod( new PaymentWithoutAssociatedData( 'KaiCoin' ) );

		$this->assertFalse( $this->donationValidator->validate( $donation )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $donation ),
			'zahlweise'
		);
	}

	public function testDirectDebitMissingBankData_validatorReturnsFalse() {
		$donation = $this->newDonationWithPaymentMethod(
			new DirectDebitPayment( $this->newEmptyBankData() )
		);

		$validationResult = $this->donationValidator->validate( $donation );
		$this->assertFalse( $validationResult->isSuccessful() );
		$this->assertConstraintWasViolated( $validationResult, 'iban' );
		$this->assertConstraintWasViolated( $validationResult, 'bic' );
		$this->assertConstraintWasViolated( $validationResult, 'bankname' );
	}

	private function newEmptyBankData(): BankData {
		$bankData = new BankData();
		$bankData->setIban( new Iban( '' ) );
		$bankData->setBic( '' );
		$bankData->setAccount( '' );
		$bankData->setBankCode( '' );
		$bankData->setBankName( '' );
		return $bankData;
	}

	private function newDonationValidator(): DonationValidator {
		return new DonationValidator(
			new AmountValidator( 1 ),
			new AmountPolicyValidator( 1000, 200, 300 ),
			$this->newMockPersonalInfoValidator(),
			new TextPolicyValidator(),
			new AllowedValuesValidator( [ PaymentType::DIRECT_DEBIT, PaymentType::BANK_TRANSFER ] ),
			$this->newBankDataValidator()
		);
	}

	private function newDonation(): Donation {
		return ValidDonation::newDirectDebitDonation();
	}

	private function newBankDataValidator(): BankDataValidator {
		$ibanValidatorMock = $this->getMockBuilder( IbanValidator::class )->disableOriginalConstructor()->getMock();
		$ibanValidatorMock->method( 'validate' )
			->willReturn( new ValidationResult() );

		return new BankDataValidator( $ibanValidatorMock );
	}

	private function newMockPersonalInfoValidator(): PersonalInfoValidator {
		$validator = $this->getMockBuilder( PersonalInfoValidator::class )->disableOriginalConstructor()->getMock();
		$validator->method( 'validate' )->willReturn( new ValidationResult() );
		return $validator;
	}

}
