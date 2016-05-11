<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\Domain\Model\BankTransferPayment;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonationConfirmationMailer extends TemplateBasedMailer {

	public function sendMailFromDonation( Donation $donation ) {
		$this->sendMail(
			new EmailAddress( $donation->getDonor()->getEmailAddress() ),
			$this->getConfirmationMailTemplateArguments( $donation )
		);
	}

	private function getConfirmationMailTemplateArguments( Donation $donation ): array {
		return [
			'recipient' => [
				'lastName' => $donation->getDonor()->getPersonName()->getLastName(),
				'salutation' =>	$donation->getDonor()->getPersonName()->getSalutation(),
				'title' => $donation->getDonor()->getPersonName()->getTitle()
			],
			'donation' => [
				'id' => $donation->getId(),
				'amount' => $donation->getAmount()->getEuroFloat(), // number is formatted in template
				'interval' => $donation->getPaymentIntervalInMonths(),
				'needsModeration' => $donation->needsModeration(),
				'paymentType' => $donation->getPaymentType(),
				'bankTransferCode' => $this->getBankTransferCode( $donation->getPaymentMethod() ),
			]
		];
	}

	private function getBankTransferCode( PaymentMethod $paymentMethod ): string {
		if ( $paymentMethod instanceof BankTransferPayment ) {
			return $paymentMethod->getBankTransferCode();
		}

		return '';
	}
}
