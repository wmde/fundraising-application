<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\PayPalNotificationRequest;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidPayPalNotificationRequest {

	const DONATION_ID = 12345;
	const TRANSACTION_ID = '61E67681CH3238416';
	const PAYER_ID = 'LPLWNMTBWMFAY';
	const SUBSCRIBER_ID = '8RHHUM3W3PRH7QY6B59';
	const PAYER_EMAIL = 'payer.email@address.com';
	const PAYER_STATUS = 'verified';
	const PAYER_FIRST_NAME = 'Generous';
	const PAYER_LAST_NAME = 'Donor';
	const PAYER_ADDRESS_NAME = 'Generous Donor';
	const PAYER_ADDRESS_STREET = '123, Some Street';
	const PAYER_ADDRESS_POSTAL_CODE = '123456';
	const PAYER_ADDRESS_CITY = 'Some City';
	const PAYER_ADDRESS_COUNTRY_CODE = 'DE';
	const PAYER_ADDRESS_STATUS = 'confirmed';
	const TOKEN = 'my_secret_token';
	const CURRENCY_CODE = 'EUR';
	const TRANSACTION_FEE_CENTS = 27;
	const AMOUNT_GROSS_CENTS = 500;
	const SETTLE_AMOUNT_CENTS = 123;
	const PAYMENT_TIMESTAMP = '20:12:59 Jan 13, 2009 PST';
	const PAYMENT_TYPE = 'instant';

	const PAYMENT_STATUS_COMPLETED = 'Completed';
	const PAYMENT_STATUS_PENDING = 'Pending';

	public static function newInstantPaymentForDonation( int $donationId ): PayPalNotificationRequest {
		return self::newBaseRequest()
			->setDonationId( $donationId )
			->setTransactionType( 'express_checkout' )
			->setPaymentStatus( self::PAYMENT_STATUS_COMPLETED );
	}

	public static function newPendingPayment(): PayPalNotificationRequest {
		return self::newBaseRequest()
			->setDonationId( self::DONATION_ID )
			->setTransactionType( 'express_checkout' )
			->setPaymentStatus( self::PAYMENT_STATUS_PENDING );
	}

	public static function newSubscriptionModification(): PayPalNotificationRequest {
		return self::newBaseRequest()
			->setDonationId( self::DONATION_ID )
			->setTransactionType( 'subscr_modify' )
			->setPaymentStatus( self::PAYMENT_STATUS_COMPLETED );
	}

	private static function newBaseRequest(): PayPalNotificationRequest {
		return ( new PayPalNotificationRequest() )
			->setTransactionId( self::TRANSACTION_ID )
			->setPayerId( self::PAYER_ID )
			->setSubscriberId( self::SUBSCRIBER_ID )
			->setPayerEmail( self::PAYER_EMAIL )
			->setPayerStatus( self::PAYER_STATUS )
			->setPayerFirstName( self::PAYER_FIRST_NAME )
			->setPayerLastName( self::PAYER_LAST_NAME )
			->setPayerAddressName( self::PAYER_ADDRESS_NAME )
			->setPayerAddressStreet( self::PAYER_ADDRESS_STREET )
			->setPayerAddressPostalCode( self::PAYER_ADDRESS_POSTAL_CODE )
			->setPayerAddressCity( self::PAYER_ADDRESS_CITY )
			->setPayerAddressCountryCode( self::PAYER_ADDRESS_COUNTRY_CODE )
			->setPayerAddressStatus( self::PAYER_ADDRESS_STATUS )
			->setToken( self::TOKEN )
			->setCurrencyCode( self::CURRENCY_CODE )
			->setTransactionFee( Euro::newFromCents( self::TRANSACTION_FEE_CENTS ) )
			->setAmountGross( Euro::newFromCents( self::AMOUNT_GROSS_CENTS ) )
			->setSettleAmount( Euro::newFromCents( self::SETTLE_AMOUNT_CENTS ) )
			->setPaymentTimestamp( self::PAYMENT_TIMESTAMP )
			->setPaymentStatus( self::PAYMENT_STATUS_COMPLETED )
			->setPaymentType( self::PAYMENT_TYPE );
	}

}
