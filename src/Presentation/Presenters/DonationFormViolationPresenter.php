<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\DonationContext\UseCases\AddDonation\AddDonationValidationResult as Result;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DonationFormPresenter\ImpressionCounts;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\PaymentContext\Domain\BankDataValidationResult;
use WMDE\Fundraising\PaymentContext\Domain\PaymentType;
use WMDE\FunValidators\ConstraintViolation;

/**
 * @license GPL-2.0-or-later
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonationFormViolationPresenter {

	private TwigTemplate $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	/**
	 * @param ConstraintViolation[] $violations
	 * @param AddDonationRequest $request
	 * @param ImpressionCounts $trackingData
	 * @return string
	 */
	public function present( array $violations, AddDonationRequest $request, ImpressionCounts $trackingData ): string {
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
				'amount' => $request->getAmount()->getEuroCents(),
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
		if ( $request->getPaymentType() !== PaymentType::DirectDebit->value ) {
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

			Result::SOURCE_DONOR_ADDRESS_TYPE => 'address',
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
			static function ( $validationResult, ConstraintViolation $violation ) use ( $fieldGroups ) {
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
