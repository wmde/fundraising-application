<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorType;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\Validators\AddressValidator;

class ValidateAddressController {

	private const VIOLATION_UNKNOWN_ADDRESS_TYPE = 'address_form_error';

	private AddressValidator $addressValidator;

	public function index( Request $request, FunFunFactory $ffFactory ): Response {
		$this->addressValidator = $ffFactory->newAddressValidator();
		$isCompanyWithContact = $this->handleCompanyWithContact( $request );
		$addressType = $request->get( 'addressType', '' );

		try {
			$donorType = $this->getAddressType( $addressType );
		} catch ( \UnexpectedValueException $e ) {
			return $this->newErrorResponse(
				new ConstraintViolation(
					$request->get( 'addressType', '' ),
					self::VIOLATION_UNKNOWN_ADDRESS_TYPE,
					'addressType'
				)
			);
		}

		$nameViolations = [];

		switch ( $donorType ) {
			case DonorType::PERSON:
			case DonorType::EMAIL:
				$nameViolations = $this->getPersonViolations( $request );
				break;
			case DonorType::COMPANY:
				$nameViolations = $this->getCompanyViolations( $request );
				if ( $isCompanyWithContact ) {
					$nameViolations = array_merge( $nameViolations, $this->getPersonViolations( $request ) );
				}
				break;
			case DonorType::ANONYMOUS:
				return $this->newSuccessResponse();
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

	private function handleCompanyWithContact( Request $request ): bool {
		if ( $request->get( 'addressType', '' ) !== 'company_with_contact' ) {
			return false;
		}

		$request->query->set( 'addressType', AddressType::LEGACY_COMPANY );
		return true;
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

	private function getAddressType( string $addressType ): DonorType {
		return AddressType::presentationAddressTypeToDonorType( $addressType );
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
