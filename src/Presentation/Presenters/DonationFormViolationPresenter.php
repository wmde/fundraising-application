<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationValidationResult as Result;
use WMDE\Fundraising\PaymentContext\Domain\BankDataValidationResult;
use WMDE\Fundraising\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\Presentation\AmountFormatter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ConstraintViolation;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonationFormViolationPresenter {

	private $template;
	private $amountFormatter;

	public function __construct( TwigTemplate $template, AmountFormatter $amountFormatter ) {
		$this->template = $template;
		$this->amountFormatter = $amountFormatter;
	}

	/**
	 * @param ConstraintViolation[] $violations
	 * @param AddDonationRequest $request
	 * @param DonationTrackingInfo $trackingData
	 * @return string
	 */
	public function present( array $violations, AddDonationRequest $request, DonationTrackingInfo $trackingData ): string {
		return $this->template->render(
			[
				'initialFormValues' => $this->getDonationFormArguments( $request ),
				'violatedFields' => $this->getViolatedFields( $violations ),
				'validationResult' => $this->getValidationResult( $violations ),
				'tracking' => [
					'impressionCount' => $trackingData->getTotalImpressionCount(),
					'bannerImpressionCount' => $trackingData->getSingleBannerImpressionCount()
				]
			]
		);
	}

	private function getDonationFormArguments( AddDonationRequest $request ): array {
		return array_merge(
			[
				'amount' => $this->amountFormatter->format( $request->getAmount() ),
				'paymentType' => $request->getPaymentType(),
				'paymentIntervalInMonths' => $request->getInterval(),
			],
			$this->getBankData( $request ),
			$this->getPersonalInfo( $request )
		);
	}

	private function getPersonalInfo( AddDonationRequest $request ): array {
		if ( $request->donorIsAnonymous() ) {
			return [];
		}

		return array_merge(
			$this->getPersonName( $request ),
			$this->getPhysicalAddress( $request ),
			[ 'email' => $request->getDonorEmailAddress() ]
		);
	}

	private function getPersonName( AddDonationRequest $request ): array {
		return [
			'addressType' => $request->getDonorType(),
			'salutation' => $request->getDonorSalutation(),
			'title' => $request->getDonorTitle(),
			'companyName' => $request->getDonorCompany(),
			'firstName' => $request->getDonorFirstName(),
			'lastName' => $request->getDonorLastName(),
		];
	}

	private function getPhysicalAddress( AddDonationRequest $request ): array {
		return [
			'street' => $request->getDonorStreetAddress(),
			'postcode' => $request->getDonorPostalCode(),
			'city' => $request->getDonorCity(),
			'country' => $request->getDonorCountryCode(),
		];
	}

	/**
	 * @param ConstraintViolation[] $violations
	 * @return array
	 */
	private function getViolatedFields( array $violations ): array {
		$fieldNames = [];
		foreach ( $violations as $violation ) {
			$fieldNames[$violation->getSource()] = $violation->getMessageIdentifier();
		}

		return $fieldNames;
	}

	private function getBankData( AddDonationRequest $request ): array {
		if ( $request->getPaymentType() !== PaymentMethod::DIRECT_DEBIT ) {
			return [];
		}
		$bankData = $request->getBankData();
		return [
			'iban' => $bankData->getIban()->toString(),
			'bic' => $bankData->getBic(),
			'bankName' => $bankData->getBankName()
		];
	}

	private function getValidationResult( array $violations ): array {
		$fieldGroups = [
			Result::SOURCE_PAYMENT_AMOUNT => 'paymentData',
			Result::SOURCE_PAYMENT_TYPE => 'paymentData',

			BankDataValidationResult::SOURCE_BANK_ACCOUNT => 'bankData',
			BankDataValidationResult::SOURCE_BANK_CODE => 'bankData',
			BankDataValidationResult::SOURCE_IBAN => 'bankData',
			BankDataValidationResult::SOURCE_BIC => 'bankData',

			Result::SOURCE_DONOR_EMAIL => 'address',
			Result::SOURCE_DONOR_COMPANY => 'address',
			Result::SOURCE_DONOR_FIRST_NAME => 'address',
			Result::SOURCE_DONOR_LAST_NAME => 'address',
			Result::SOURCE_DONOR_SALUTATION => 'address',
			Result::SOURCE_DONOR_TITLE => 'address',
			Result::SOURCE_DONOR_STREET_ADDRESS => 'address',
			Result::SOURCE_DONOR_POSTAL_CODE => 'address',
			Result::SOURCE_DONOR_CITY => 'address',
			Result::SOURCE_DONOR_COUNTRY => 'address',

		];
		return array_reduce(
			$violations,
			function( $validationResult, ConstraintViolation $violation ) use ( $fieldGroups ) {
				$validationResult[$fieldGroups[$violation->getSource()]] = false;
				return $validationResult;
			},
			[
				'paymentData' => true,
				'bankData' => true,
				'address' => true,
			]
		);
	}

}
