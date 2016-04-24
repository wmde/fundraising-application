<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\PayPalPaymentNotificationVerifierException;
use WMDE\Fundraising\Frontend\UseCases\HandlePayPalPaymentNotification\PayPalNotificationRequest;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PayPalNotificationHandler {

	private $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		$this->ffFactory = $ffFactory;
	}

	public function handle( Request $request ): Response {
		try {
			$post = $request->request;
			$this->ffFactory->getPayPalPaymentNotificationVerifier()->verify( $post->all() );

			// TODO: check txn_type
			// TODO: update donation's status and payment provider related fields

			$useCase = $this->ffFactory->newHandlePayPalPaymentNotificationUseCase( $this->getUpdateToken( $post ) );

			$useCase->handleNotification(
				( new PayPalNotificationRequest() )
					->setTransactionType( $post->get( 'txn_type', '' ) )
					->setTransactionId( $post->get( 'txn_id', '' ) )
					->setPayerId( $post->get( 'payer_id', '' ) )
					->setSubscriberId( $post->get( 'subscr_id', '' ) )
					->setPayerEmail( $post->get( 'payer_email', '' ) )
					->setPayerStatus( $post->get( 'payer_status', '' ) )
					->setPayerFirstName( $post->get( 'first_name', '' ) )
					->setPayerLastName( $post->get( 'last_name', '' ) )
					->setPayerAddressName( $post->get( 'address_name', '' ) )
					->setPayerAddressStreet( $post->get( 'address_street', '' ) )
					->setPayerAddressPostalCode( $post->get( 'address_zip', '' ) )
					->setPayerAddressCity( $post->get( 'address_city', '' ) )
					->setPayerAddressCountryCode( $post->get( 'address_country_code', '' ) )
					->setPayerAddressStatus( $post->get( 'address_status', '' ) )
					->setDonationId( $post->get( 'item_number', 0 ) )
					->setCurrencyCode( $post->get( 'mc_currency', '' ) )
					->setTransactionFee( Euro::newFromString( $post->get( 'mc_fee', '0' ) ) )
					->setAmountGross( Euro::newFromString( $post->get( 'mc_gross', '0' ) ) )
					->setSettleAmount( Euro::newFromString( $post->get( 'settle_amount', '0' ) ) )
					->setPaymentTimestamp( $post->get( 'payment_date', '' ) )
					->setPaymentStatus( $post->get( 'payment_status', '' ) )
					->setPaymentType( $post->get( 'payment_type', '' ) )
			);

			return new Response( '', 200 );
		} catch ( PayPalPaymentNotificationVerifierException $e ) {
			// TODO: log error
			// TODO: let PayPal resend IPN?
		}

		return new Response( 'TODO' ); # PayPal expects an empty response
	}

	private function getUpdateToken( ParameterBag $postRequest ): string {
		return $this->getValueFromCustomVars( $postRequest->get( 'custom', '' ), 'utoken' );
	}

	private function getValueFromCustomVars( string $customVars, string $key ): string {
		$vars = json_decode( $customVars, true );
		return !empty( $vars[$key] ) ? $vars[$key] : '';
	}

}