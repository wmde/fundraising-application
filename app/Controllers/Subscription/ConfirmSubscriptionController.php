<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Subscription;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ConfirmSubscriptionController {

	public function index( FunFunFactory $ffFactory, string $confirmationCode ): Response {
		$useCase = $ffFactory->newConfirmSubscriptionUseCase();
		$result = $useCase->confirmSubscription( $confirmationCode );
		return new Response( $ffFactory->newConfirmSubscriptionHtmlPresenter()->present( $result ) );
	}
}
