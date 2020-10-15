<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Subscription;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ConfirmSubscriptionController {

	public function handle( FunFunFactory $ffFactory, string $confirmationCode ) {
		$useCase = $ffFactory->newConfirmSubscriptionUseCase();
		$response = $useCase->confirmSubscription( $confirmationCode );
		return $ffFactory->newConfirmSubscriptionHtmlPresenter()->present( $response );
	}
}
