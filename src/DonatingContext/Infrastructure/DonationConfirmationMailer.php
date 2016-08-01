<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\Infrastructure;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipApplicationContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DonationConfirmationMailer {

	private $mailer;

	public function __construct( TemplateBasedMailer $mailer ) {
		$this->mailer = $mailer;
	}

	public function sendConfirmationMailFor( Donation $donation ) {
		$this->mailer->sendMail(
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
