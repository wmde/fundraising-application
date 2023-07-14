<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Factories;

use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\Order;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\OrderParameters;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\Product;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\Subscription;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\SubscriptionParameters;
use WMDE\Fundraising\PaymentContext\Services\PayPal\Model\SubscriptionPlan;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PaypalAPI;

class PayPalAPIStub implements PaypalAPI {
	private const MESSAGE = 'This PayPal API stub should be overwritten from the environment-specific factories!';

	public function listProducts(): array {
		throw new \LogicException( self::MESSAGE );
	}

	public function createProduct( Product $product ): Product {
		throw new \LogicException( self::MESSAGE );
	}

	public function listSubscriptionPlansForProduct( string $productId ): array {
		throw new \LogicException( self::MESSAGE );
	}

	public function createSubscriptionPlanForProduct( SubscriptionPlan $subscriptionPlan ): SubscriptionPlan {
		throw new \LogicException( self::MESSAGE );
	}

	public function createSubscription( SubscriptionParameters $subscriptionParameters ): Subscription {
		throw new \LogicException( self::MESSAGE );
	}

	public function createOrder( OrderParameters $orderParameters ): Order {
		throw new \LogicException( self::MESSAGE );
	}
}
