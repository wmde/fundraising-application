<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Application {

	public const ACTIVE_MEMBERSHIP = 'active';
	public const SUSTAINING_MEMBERSHIP = 'sustaining';

	private const NO_MODERATION_NEEDED = false;
	private const NEEDS_MODERATION = true;

	private const IS_CURRENT = false;
	private const IS_CANCELLED = true;

	private const IS_CONFIRMED = true;
	private const IS_PENDING = false;

	const IS_DELETED = true;
	const IS_NOT_DELETED = false;

	/**
	 * @var int|null
	 */
	private $id;

	private $type;
	private $applicant;
	private $payment;
	private $needsModeration;
	private $isCancelled;
	private $isConfirmed;
	private $isDeleted;

	public static function newApplication( string $type, Applicant $applicant, Payment $payment ): self {
		return new self(
			null,
			$type,
			$applicant,
			$payment,
			self::NO_MODERATION_NEEDED,
			self::IS_CURRENT,
			self::IS_PENDING,
			self::IS_NOT_DELETED
		);
	}

	public function __construct( ?int $id, string $type, Applicant $applicant, Payment $payment,
		bool $needsModeration, bool $isCancelled, bool $isConfirmed, bool $isDeleted ) {
		$this->id = $id;
		$this->type = $type;
		$this->applicant = $applicant;
		$this->payment = $payment;
		$this->needsModeration = $needsModeration;
		$this->isCancelled = $isCancelled;
		$this->isConfirmed = $isConfirmed;
		$this->isDeleted = $isDeleted;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function hasId(): bool {
		return $this->id !== null;
	}

	public function getApplicant(): Applicant {
		return $this->applicant;
	}

	public function getPayment(): Payment {
		return $this->payment;
	}

	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param int $id
	 * @throws \RuntimeException
	 */
	public function assignId( int $id ) {
		if ( $this->id !== null && $this->id !== $id ) {
			throw new \RuntimeException( 'Id cannot be changed after initial assignment' );
		}

		$this->id = $id;
	}

	public function cancel() {
		$this->isCancelled = self::IS_CANCELLED;
	}

	public function markForModeration() {
		$this->needsModeration = self::NEEDS_MODERATION;
	}

	public function isCancelled(): bool {
		return $this->isCancelled === self::IS_CANCELLED;
	}

	public function needsModeration(): bool {
		return $this->needsModeration === self::NEEDS_MODERATION;
	}

	public function isConfirmed(): bool {
		return $this->isConfirmed === self::IS_CONFIRMED;
	}

	public function confirm() {
		$this->isConfirmed = self::IS_CONFIRMED;
	}

	/**
	 * @param PayPalData $payPalData
	 * @throws RuntimeException
	 */
	public function addPayPalData( PayPalData $payPalData ) {
		$paymentMethod = $this->payment->getPaymentMethod();

		if ( !( $paymentMethod instanceof PayPalPayment ) ) {
			throw new RuntimeException( 'Cannot set PayPal data on a non PayPal payment' );
		}

		$paymentMethod->addPayPalData( $payPalData );
	}

	public function confirmSubscriptionCreated() {
		if ( !$this->hasExternalPayment() ) {
			throw new RuntimeException( 'Only external payments can be confirmed as booked' );
		}

		if ( !$this->statusAllowsForBooking() ) {
			throw new RuntimeException( 'Only unconfirmed membership applications can be confirmed as booked' );
		}

		$this->confirm();
	}

	public function hasExternalPayment(): bool {
		return $this->getPayment()->getPaymentMethod()->getType() === PaymentType::PAYPAL;
	}

	private function statusAllowsForBooking(): bool {
		return !$this->isConfirmed() || $this->needsModeration() || $this->isCancelled();
	}

	public function markAsDeleted() {
		$this->isDeleted = self::IS_DELETED;
	}

	public function isDeleted(): bool {
		return $this->isDeleted;
	}

	public function setFirstPaymentDate( string $firstPaymentDate ) {
		$paymentMethod = $this->getPayment()->getPaymentMethod();
		if ( $paymentMethod instanceof PayPalPayment ) {
			$payPalData = $paymentMethod->getPayPalData() ?: new PayPalData();
			$payPalData->setFirstPaymentDate( $firstPaymentDate );
			$this->addPayPalData( $payPalData );
		}
	}

}
