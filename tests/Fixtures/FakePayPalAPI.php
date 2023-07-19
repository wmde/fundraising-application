<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\Order;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\OrderParameters;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\Product;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\Subscription;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\SubscriptionParameters;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\SubscriptionPlan;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PaypalAPI;

class FakePayPalAPI implements PaypalAPI {

	private const TESTDATE = '2004-04-04';

	public function listProducts(): array {
		throw new \LogicException( "Not implemented, your test should not use this method!" );
	}

	public function createProduct( Product $product ): Product {
		throw new \LogicException( "Not implemented, your test should not use this method!" );
	}

	public function listSubscriptionPlansForProduct( string $productId ): array {
		throw new \LogicException( "Not implemented, your test should not use this method!" );
	}

	public function createSubscriptionPlanForProduct( SubscriptionPlan $subscriptionPlan ): SubscriptionPlan {
		throw new \LogicException( "Not implemented, your test should not use this method!" );
	}

	public function createSubscription( SubscriptionParameters $subscriptionParameters ): Subscription {
		return new Subscription(
			"42",
			new \DateTimeImmutable( self::TESTDATE ),
			"https://sandbox.paypal.com/"
		);
	}

	public function createOrder( OrderParameters $orderParameters ): Order {
		return new Order( 'ID', 'https://sandbox.paypal.com/' );
	}
}
