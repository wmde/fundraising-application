<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\PaymentContext\UseCases\GenerateIban\GenerateIbanRequest;

/**
 * @license GNU GPL v2+
 */
class IbanController {

	public function validateIban( Request $request, FunFunFactory $ffFactory ): Response {
		$useCase = $ffFactory->newCheckIbanUseCase();
		$checkIbanResponse = $useCase->checkIban( new Iban( $request->query->get( 'iban', '' ) ) );
		return new JsonResponse( $ffFactory->newIbanPresenter()->present( $checkIbanResponse ) );
	}

	public function convertBankDataToIban( Request $request, FunFunFactory $ffFactory ): Response {
		$generateIbanRequest = new GenerateIbanRequest(
			$request->query->get( 'accountNumber', '' ),
			$request->query->get( 'bankCode', '' )
		);

		$generateIbanResponse = $ffFactory->newGenerateIbanUseCase()->generateIban( $generateIbanRequest );
		return new JsonResponse( $ffFactory->newIbanPresenter()->present( $generateIbanResponse ) );

	}
}