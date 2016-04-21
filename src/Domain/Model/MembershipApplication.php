<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplication {

	const ACTIVE_MEMBERSHIP = 'active';
	const SUSTAINING_MEMBERSHIP = 'sustaining';

	const NO_MODERATION_NEEDED = false;
	const NEEDS_MODERATION = true;

	const IS_CURRENT = 0;
	const IS_CANCELLED = 1;

	/**
	 * @var int|null
	 */
	private $id;

	private $type;
	private $applicant;
	private $payment;
	private $needsModeration;
	private $isCancelled;

	public static function newApplication( string $type, MembershipApplicant $applicant, MembershipPayment $payment ): self {
		return new self(
			null,
			$type,
			$applicant,
			$payment,
			self::NO_MODERATION_NEEDED,
			self::IS_CURRENT
		);
	}

	public function __construct( int $id = null, string $type, MembershipApplicant $applicant, MembershipPayment $payment,
		bool $needsModeration, int $isCancelled ) {

		$this->id = $id;
		$this->type = $type;
		$this->applicant = $applicant;
		$this->payment = $payment;
		$this->needsModeration = $needsModeration;
		$this->isCancelled = $isCancelled;
	}

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	public function hasId(): bool {
		return $this->id !== null;
	}

	public function getApplicant(): MembershipApplicant {
		return $this->applicant;
	}

	public function getPayment(): MembershipPayment {
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

}
