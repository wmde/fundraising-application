<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\RequestModel;

use Sofort\SofortLib\Notification as VendorNotification;
use DateTime;

class SofortNotificationRequest {

	/**
	 * @var int
	 */
	private $donationId;
	/**
	 * @var string
	 */
	private $transactionId;
	/**
	 * @var DateTime
	 */
	private $time;

	public function getDonationId(): ?int {
		return $this->donationId;
	}

	public function setDonationId( int $donationId ): self {
		$this->donationId = $donationId;
		return $this;
	}

	public function getTransactionId(): ?string {
		return $this->transactionId;
	}

	public function setTransactionId( string $transactionId ): self {
		$this->transactionId = $transactionId;
		return $this;
	}

	public function getTime(): DateTime {
		return $this->time;
	}

	public function setTime( DateTime $time ): self {
		$this->time = $time;
		return $this;
	}

	public static function fromRawRequest( string $content ): ?self {
		$vendorNotification = new VendorNotification();
		$result = $vendorNotification->getNotification( $content );

		if ( $result === false ) {
			return null;
		}

		$time = DateTime::createFromFormat( DateTime::ATOM, $vendorNotification->getTime() );

		if ( $time === false ) {
			return null;
		}

		$notification = new self();
		$notification->setTime( $time );
		$notification->setTransactionId( $vendorNotification->getTransactionId() );

		return $notification;
	}
}
