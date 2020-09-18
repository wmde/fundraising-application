<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\Validators\AddressValidator;

/**
 * @license GPL-2.0-or-later
 */
class ValidateAddressController {

	private const VIOLATION_UNKNOWN_ADDRESS_TYPE = 'address_form_error';

	private AddressValidator $addressValidator;

	public function validate( Request $request, FunFunFactory $ffFactory ): Response {
		$this->addressValidator = $ffFactory->newAddressValidator();

		$addressType = $this->getAddressType( $request );
		if ( $addressType === AddressType::PERSON ) {
			$nameViolations = $this->getPersonViolations( $request );
		} elseif ( $addressType === AddressType::COMPANY ) {
			$nameViolations = $this->getCompanyViolations( $request );
		} elseif ( $addressType === AddressType::ANONYMOUS ) {
			return $this->newSuccessResponse();
		} else {
			return $this->newErrorResponse(
				new ConstraintViolation(
					$addressType,
					self::VIOLATION_UNKNOWN_ADDRESS_TYPE,
					'addressType'
				)
			);
		}

		$violations = array_merge(
			$nameViolations,
			$this->getAddressViolations( $request )
		);

		if ( empty( $violations ) ) {
			return $this->newSuccessResponse();
		}

		return $this->newErrorResponse( ...$violations );
	}

	private function getPersonViolations( Request $request ): array {
		return $this->addressValidator->validatePersonName(
			$request->get( 'salutation', '' ),
			$request->get( 'title', '' ),
			$request->get( 'firstName', '' ),
			$request->get( 'lastName', '' )
		)->getViolations();
	}

	private function getCompanyViolations( Request $request ): array {
		return $this->addressValidator->validateCompanyName(
			$request->get( 'companyName', '' )
		)->getViolations();
	}

	private function getAddressViolations( Request $request ): array {
		return $this->addressValidator->validatePostalAddress(
			$request->get( 'street', '' ),
			$request->get( 'postcode', '' ),
			$request->get( 'city', '' ),
			$request->get( 'country', '' )
		)->getViolations();
	}

	private function getAddressType( Request $request ): string {
		$addressType = $request->get( 'addressType', '' );
		try {
			return AddressType::presentationAddressTypeToDomainAddressType( $addressType );
		} catch ( \UnexpectedValueException $ex ) {
			return $addressType;
		}
	}

	private function newSuccessResponse(): Response {
		return new JsonResponse( [ 'status' => 'OK' ] );
	}

	private function newErrorResponse( ConstraintViolation ...$violations ): Response {
		$errors = [];

		foreach ( $violations as $violation ) {
			$errors[$violation->getSource()] = $violation->getMessageIdentifier();
		}

		return new JsonResponse( [ 'status' => 'ERR', 'messages' => $errors ] );
	}

}
