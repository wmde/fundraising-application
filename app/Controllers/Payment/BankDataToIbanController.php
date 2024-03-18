<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Payment;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class BankDataToIbanController {

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$generateIbanResponse = $ffFactory->newGenerateBankDataFromGermanLegacyBankDataUseCase()->generateIban(
			$request->query->get( 'accountNumber', '' ),
			$request->query->get( 'bankCode', '' )
		);
		return new JsonResponse( $ffFactory->newIbanPresenter()->present( $generateIbanResponse ) );
	}
}
