<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;

class ValidateIbanController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$useCase = $ffFactory->newCheckIbanUseCase();
		$checkIbanResponse = $useCase->checkIban( new Iban( $request->query->get( 'iban', '' ) ) );
		return new JsonResponse( $ffFactory->newIbanPresenter()->present( $checkIbanResponse ) );
	}
}
