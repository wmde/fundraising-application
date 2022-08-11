<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Domain\MembershipPaymentValidator;
use WMDE\Fundraising\MembershipContext\UseCases\ValidateMembershipFee\ValidateMembershipFeeUseCase;
use WMDE\FunValidators\ConstraintViolation;

class ValidateMembershipPaymentController {

	/**
	 * @var ConstraintViolation[]
	 */
	private array $validationErrors = [];

	public function index( Request $httpRequest, FunFunFactory $factory ): Response {
		$response = ( new ValidateMembershipFeeUseCase( $factory->newPaymentServiceFactory() ) )->validate(
			$this->euroFromRequest( $httpRequest )->getEuros(),
			(int)$httpRequest->request->get( 'paymentIntervalInMonths', '0' ),
			$httpRequest->request->get( 'addressType', '' ),
			$httpRequest->request->get( 'paymentType', '' )
		);

		if ( $response->isSuccessful() ) {
			return new JsonResponse( [ 'status' => 'OK' ] );
		}

		$this->validationErrors = array_merge(
			$response->getValidationErrors(),
			$this->validationErrors,
		);
		return $this->newJsonErrorResponse();
	}

	private function euroFromRequest( Request $httpRequest ): Euro {
		$currentFeeString = $httpRequest->request->get( 'membershipFee', '' );
		if ( !ctype_digit( $currentFeeString ) ) {
			$this->validationErrors[] = new ConstraintViolation(
				null,
				'cannot_parse_fee',
				MembershipPaymentValidator::SOURCE_MEMBERSHIP_FEE
			);
			return Euro::newFromCents( 0 );
		}
		return Euro::newFromCents(
			intval( $currentFeeString )
		);
	}

	private function newJsonErrorResponse(): Response {
		$messages = [];
		foreach ( $this->validationErrors as $error ) {
			$messages[ $error->getSource() ] = $error->getMessageIdentifier();
		}
		return new JsonResponse( [
			'status' => 'ERR',
			'messages' => $messages
		] );
	}

}
