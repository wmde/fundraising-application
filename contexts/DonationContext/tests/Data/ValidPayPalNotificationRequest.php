<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Data;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\HandlePayPalPaymentNotification\PayPalNotificationRequest;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidPayPalNotificationRequest {

	const DONATION_ID = 12345;
	const TRANSACTION_ID = '61E67681CH3238416';
	const PAYER_ID = 'LPLWNMTBWMFAY';
	const SUBSCRIBER_ID = '8RHHUM3W3PRH7QY6B59';
	const PAYER_EMAIL = 'foerderpp@wikimedia.de';
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
	const TRANSACTION_FEE_EURO_STRING = '2.70';
	const AMOUNT_GROSS_CENTS = 500;
	const AMOUNT_GROSS_EURO_STRING = '5.00';
	const SETTLE_AMOUNT_CENTS = 123;
	const SETTLE_AMOUNT_EURO_STRING = '1.23';
	const PAYMENT_TIMESTAMP = '20:12:59 Jan 13, 2009 PST';
	const PAYMENT_TYPE = 'instant';
	const ITEM_NAME = 'Spende an Wikimdia Deutschland';
	const ITEM_NUMBER = 1;

	const PAYMENT_STATUS_COMPLETED = 'Completed';
	const PAYMENT_STATUS_PENDING = 'Pending';

	public static function newInstantPaymentForDonation( int $donationId ): PayPalNotificationRequest {
		return self::newBaseRequest()
			->setDonationId( $donationId )
			->setTransactionType( 'express_checkout' )
			->setPaymentStatus( self::PAYMENT_STATUS_COMPLETED );
	}

	public static function newDuplicatePaymentForDonation( int $donationId,
														   string $transactionid ): PayPalNotificationRequest {
		return self::newBaseRequest()
			->setDonationId( $donationId )
			->setTransactionType( 'express_checkout' )
			->setTransactionId( $transactionid )
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

	public static function newRecurringPayment( int $donationId ): PayPalNotificationRequest {
		return self::newBaseRequest()
			->setDonationId( $donationId )
			->setTransactionType( 'subscr_payment' )
			->setPaymentStatus( self::PAYMENT_STATUS_COMPLETED );
	}

	public static function newHttpParamsForSubscriptionModification(): array {
		return [
			'receiver_email' => self::PAYER_EMAIL,
			'payment_status' => self::PAYMENT_STATUS_COMPLETED,
			'payer_id' => self::PAYER_ID,
			'subscr_id' => self::SUBSCRIBER_ID,
			'payer_status' => self::PAYER_STATUS,
			'address_status' => self::PAYER_ADDRESS_STATUS,
			'mc_gross' => self::AMOUNT_GROSS_EURO_STRING,
			'mc_currency' => self::CURRENCY_CODE,
			'mc_fee' => self::TRANSACTION_FEE_EURO_STRING,
			'settle_amount' => self::SETTLE_AMOUNT_EURO_STRING,
			'first_name' => self::PAYER_FIRST_NAME,
			'last_name' => self::PAYER_LAST_NAME,
			'address_name' => self::PAYER_ADDRESS_NAME,
			'item_name' => self::ITEM_NAME,
			'item_number' => self::ITEM_NUMBER,
			'custom' => json_encode( [
				'id' => self::DONATION_ID,
				'utoken' => self::TOKEN
			] ),
			'txn_id' => self::TRANSACTION_ID,
			'payment_type' => self::PAYMENT_TYPE,
			'txn_type' => 'subscr_modify',
			'payment_date' => self::PAYMENT_TIMESTAMP,
		];
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
			->setTransactionFee( self::TRANSACTION_FEE_EURO_STRING )
			->setAmountGross( Euro::newFromCents( self::AMOUNT_GROSS_CENTS ) )
			->setSettleAmount( Euro::newFromCents( self::SETTLE_AMOUNT_CENTS ) )
			->setPaymentTimestamp( self::PAYMENT_TIMESTAMP )
			->setPaymentStatus( self::PAYMENT_STATUS_COMPLETED )
			->setPaymentType( self::PAYMENT_TYPE );
	}

}
