<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\Validators\AddressValidator;

/**
 * @license GNU GPL v2+
 */
class ValidateAddressController {

	private const VIOLATION_UNKNOWN_ADDRESS_TYPE = 'address_form_error';

	/**
	 * @var AddressValidator
	 */
	private $addressValidator;

	public function validate( Request $request, FunFunFactory $ffFactory ): Response {
		$this->addressValidator = $ffFactory->newAddressValidator();

		if ( $this->getAddressType( $request ) === DonorName::PERSON_PRIVATE ) {
			$nameViolations = $this->getPersonViolations( $request );
		} elseif ( $this->getAddressType( $request ) === DonorName::PERSON_COMPANY ) {
			$nameViolations = $this->getCompanyViolations( $request );
		} elseif ( $this->getAddressType( $request ) === DonorName::PERSON_ANONYMOUS ) {
			return $this->newSuccessResponse();
		} else {
			return $this->newErrorResponse(
				$ffFactory->getTranslator(),
				new ConstraintViolation(
					$this->getAddressType( $request ),
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

		return $this->newErrorResponse( $ffFactory->getTranslator(), ...$violations );
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
		return $request->get( 'addressType', '' );
	}

	private function newSuccessResponse(): Response {
		return new JsonResponse( [ 'status' => 'OK' ] );
	}

	private function newErrorResponse( TranslatorInterface $translator, ConstraintViolation ...$violations ): Response {
		$errors = [];

		foreach ( $violations as $violation ) {
			$errors[$violation->getSource()] = $translator->trans( $violation->getMessageIdentifier() );
		}

		return new JsonResponse( [ 'status' => 'ERR', 'messages' => $errors ] );
	}

}