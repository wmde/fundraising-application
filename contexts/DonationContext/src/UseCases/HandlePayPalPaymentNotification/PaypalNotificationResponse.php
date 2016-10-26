<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\HandlePayPalPaymentNotification;

class PaypalNotificationResponse
{
	private $wasHandled;
	private $notificationFailed;
	private $handlingContext;

	private function __construct( bool $notficationWasHandled, bool $isError, array $handlingContext = [] )
	{
		$this->wasHandled = $notficationWasHandled;
		$this->notificationFailed = $isError;
		$this->handlingContext = $handlingContext;
	}

	public static function newSuccessResponse(): self {
		return new self( true, false );
	}

	public static function newUnhandledResponse( array $context ): self {
		return new self( false, false, $context );
	}

	public static function newFailureResponse( array $context ): self {
		return new self( false, true, $context );
	}

	public function notificationWasHandled(): bool {
		return $this->wasHandled;
	}

	public function hasErrors(): bool {
		return $this->notificationFailed;
	}

	public function getContext(): array
	{
		return $this->handlingContext;
	}
}