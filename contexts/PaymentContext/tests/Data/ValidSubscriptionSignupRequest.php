<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Tests\Data;

use WMDE\Fundraising\Frontend\MembershipContext\UseCases\HandleSubscriptionSignupNotification\SubscriptionSignupRequest;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class ValidSubscriptionSignupRequest {

	const APPLICATION_ID = 1;
	const CURRENCY_CODE = 'EUR';
	const SUBSCRIPTION_ID = 'subscr_id';
	const SUBSCRIPTION_DATE = '12:34:56 Jan 25, 2017 PST';
	const TRANSACTION_TYPE = 'subscr_signup';
	const PAYMENT_TYPE = 'instant';
	const PAYER_ID = 'payer_id';
	const PAYER_STATUS = 'verified';
	const PAYER_ADDRESS_STATUS = 'confirmed';
	const PAYER_FIRST_NAME = 'Hank';
	const PAYER_LAST_NAME = 'Scorpio';
	const PAYER_ADDRESS_NAME = 'Hank Scorpio';
	const PAYER_ADDRESS_STREET = 'Hammock District';
	const PAYER_ADDRESS_POSTAL_CODE = '12345';
	const PAYER_ADDRESS_CITY = 'Cypress Creek';
	const PAYER_ADDRESS_COUNTRY = 'US';
	const PAYER_EMAIL = 'hank.scorpio@globex.com';

	public static function newValidRequest(): SubscriptionSignupRequest {
		$request = new SubscriptionSignupRequest();
		$request->setSubscriptionId( self::SUBSCRIPTION_ID );
		$request->setSubscriptionDate( self::SUBSCRIPTION_DATE );
		$request->setTransactionType( self::TRANSACTION_TYPE );
		$request->setCurrencyCode( self::CURRENCY_CODE );

		$request->setPaymentType( self::PAYMENT_TYPE );
		$request->setPayerId( self::PAYER_ID );
		$request->setPayerStatus( self::PAYER_STATUS );
		$request->setPayerAddressStatus( self::PAYER_ADDRESS_STATUS );
		$request->setPayerFirstName( self::PAYER_FIRST_NAME );
		$request->setPayerLastName( self::PAYER_LAST_NAME );
		$request->setPayerAddressName( self::PAYER_ADDRESS_NAME );
		$request->setPayerAddressStreet( self::PAYER_ADDRESS_STREET );
		$request->setPayerAddressPostalCode( self::PAYER_ADDRESS_POSTAL_CODE );
		$request->setPayerAddressCity( self::PAYER_ADDRESS_CITY );
		$request->setPayerAddressCountry( self::PAYER_ADDRESS_COUNTRY );
		$request->setPayerEmail( self::PAYER_EMAIL );

		$request->setApplicationId( self::APPLICATION_ID );

		return $request;
	}

}
