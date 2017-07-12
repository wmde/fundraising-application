<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\SofortPaymentNotification;

use PHPUnit\Framework\TestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\UseCases\SofortPaymentNotification\SofortPaymentNotificationUseCase
 */
class SofortPaymentNotificationUseCaseTest extends TestCase {

	public function testWhenRepositoryThrowsException_errorResponseIsReturned(): void {

	}

	public function testWhenAuthorizationFails_unhandledResponseIsReturned(): void {

	}

	public function testWhenAuthorizationSucceeds_successResponseIsReturned(): void {

	}

	public function testWhenAuthorizationSucceeds_donationIsStored(): void {

	}

	public function testWhenAuthorizationSucceeds_bookingEventIsLogged(): void {

	}

	public function testWhenPaymentTypeIsNonSofort_unhandledResponseIsReturned(): void {

	}

	public function testWhenAuthorizationSucceeds_confirmationMailIsSent(): void {

	}

	public function testWhenAuthorizationSucceedsForAnonymousDonation_confirmationMailIsNotSent(): void {

	}

	public function testWhenSendingConfirmationMailFails_handlerReturnsTrue(): void {

	}

	public function testGivenExistingTransactionIdForBookedDonation_handlerReturnsFalse(): void {

	}

	public function testWhenNotificationIsForNonExistingDonation_newDonationIsCreated(): void {

	}

	public function testWhenNotificationIsForNonExistingDonation_confirmationMailIsSent(): void {

	}

	public function testWhenNotificationIsForNonExistingDonation_bookingEventIsLogged(): void {

	}
}
