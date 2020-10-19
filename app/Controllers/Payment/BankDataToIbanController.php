<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Payment;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\UseCases\GenerateIban\GenerateIbanRequest;

/**
 * @license GPL-2.0-or-later
 */
class BankDataToIbanController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$generateIbanRequest = new GenerateIbanRequest(
			$request->query->get( 'accountNumber', '' ),
			$request->query->get( 'bankCode', '' )
		);

		$generateIbanResponse = $ffFactory->newGenerateIbanUseCase()->generateIban( $generateIbanRequest );
		return new JsonResponse( $ffFactory->newIbanPresenter()->present( $generateIbanResponse ) );
	}
}
