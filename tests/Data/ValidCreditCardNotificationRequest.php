<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\DonatingContext\UseCases\CreditCardPaymentNotification\CreditCardPaymentNotificationRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ValidCreditCardNotificationRequest {

	const AMOUNT = 13.37;
	const PAYMENT_ID = 'customer.prefix-ID2tbnag4a9u';
	const CUSTOMER_ID = 'e20fb9d5281c1bca1901c19f6e46213191bb4c17';
	const SESSION_ID = 'CC13064b2620f4028b7d340e3449676213336a4d';
	const AUTH_ID = 'd1d6fae40cf96af52477a9e521558ab7';
	const TOKEN = 'my_secret_token';
	const UPDATE_TOKEN = 'my_secret_update_token';
	const TITLE = 'Your generous donation';
	const COUNTRY_CODE = 'DE';
	const CURRENCY_CODE = 'EUR';

	public static function newBillingNotification( int $donationId ): CreditCardPaymentNotificationRequest {
		return self::newBaseRequest()
			->setDonationId( $donationId )
			->setNotificationType( CreditCardPaymentNotificationRequest::NOTIFICATION_TYPE_BILLING );
	}

	private static function newBaseRequest(): CreditCardPaymentNotificationRequest {
		return ( new CreditCardPaymentNotificationRequest() )
			->setTransactionId( self::PAYMENT_ID )
			->setAmount( Euro::newFromFloat( self::AMOUNT ) )
			->setCustomerId( self::CUSTOMER_ID )
			->setSessionId( self::SESSION_ID )
			->setAuthId( self::AUTH_ID )
			->setToken( self::TOKEN )
			->setUpdateToken( self::UPDATE_TOKEN )
			->setTitle( self::TITLE )
			->setCountry( self::COUNTRY_CODE )
			->setCurrency( self::CURRENCY_CODE );
	}

}
