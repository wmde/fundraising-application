<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\DonationContext\UseCases\ValidateDonor\ValidateDonorAddressRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\FunValidators\ConstraintViolation;

/**
 * @license GNU GPL v2+
 */
class ValidateDonorController {

	public function validate( Request $request, FunFunFactory $ffFactory ): Response {
		$validationResult =
			$ffFactory->newValidateDonorUseCase()
				->validateDonor( $this->newRequestModel( $request ) );

		if ( $validationResult->isSuccessful() ) {
			return $this->newSuccessResponse();
		}

		return $this->newErrorResponse( $ffFactory->getTranslator(), ...$validationResult->getViolations() );
	}

	private function newRequestModel( Request $request ): ValidateDonorAddressRequest {
		return ValidateDonorAddressRequest::newInstance()
			->withCity( $request->get( 'city', '' ) )
			->withCompanyName( $request->get( 'companyName', '' ) )
			->withCountryCode( $request->get( 'country', '' ) )
			->withFirstName( $request->get( 'firstName', '' ) )
			->withLastName( $request->get( 'lastName', '' ) )
			->withPostalCode( $request->get( 'postcode', '' ) )
			->withSalutation( $request->get( 'salutation', '' ) )
			->withStreetAddress( $request->get( 'street', '' ) )
			->withTitle( $request->get( 'title', '' ) )
			->withType( $request->get( 'addressType', '' ) );
	}

	private function newSuccessResponse(): Response {
		return new JsonResponse( [ 'status' => 'OK' ] );
	}

	private function newErrorResponse( TranslatorInterface $translator, ConstraintViolation ...$violations ): Response {
		$errors = [];

		foreach( $violations as $violation ) {
			$errors[$violation->getSource()] = $translator->trans( $violation->getMessageIdentifier() );
		}

		return new JsonResponse( [ 'status' => 'ERR', 'messages' => $errors ] );
	}

}