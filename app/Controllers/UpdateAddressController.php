<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\AddressChange\UseCases\ChangeAddress\ChangeAddressRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @license GNU GPL v2+
 */
class UpdateAddressController {

	public function updateAddress( string $addressToken, Request $request, FunFunFactory $ffFactory ): Response {

		$addressChangeRequest = $this->newAddressChangeRequestFromParams( $addressToken, $request->request );

		$useCase = $ffFactory->newChangeAddressUseCase();
		$response = $useCase->changeAddress( $addressChangeRequest );

		if ( !$response->isSuccess() ) {
			$ffFactory->getLogger()->error( 'Address change failed', [ 'domain_errors' => $response->getErrors() ] );
			return new Response( $ffFactory->newErrorPageHtmlPresenter()->present( implode( "\n", $response->getErrors() ) ) );
		}

		return new Response( $ffFactory->getLayoutTemplate( 'AddressUpdateSuccess.html.twig' )->render( $request->request->all() ) );
	}

	private function newAddressChangeRequestFromParams( string $addressToken, ParameterBag $params ): ChangeAddressRequest {
		$request = new ChangeAddressRequest();
		$request->setIdentifier( $addressToken );

		if ( $params->get( 'addressType', '' ) === 'person' ) {
			$request->setAddressType( 'person' );
			$this->addPersonNameParams( $request, $params );
		} else {
			$request->setAddressType( 'company' );
			$this->addCompanyNameParams( $request, $params );
		}

		$this->addPostalParams( $request, $params );
		$this->addReceiptParams( $request, $params );

		$request->assertNoNullFields()->freeze();
		return $request;
	}

	private function addPersonNameParams( ChangeAddressRequest $request, ParameterBag $params ): void {
		$request->setFirstName( $params->get( 'firstName', '' ) )
			->setLastName( $params->get( 'lastName', '' ) )
			->setSalutation( $params->get( 'salutation', '' ) )
			->setTitle( $params->get( 'title', '' ) )
			->setCompany( '' );
	}

	private function addCompanyNameParams( ChangeAddressRequest $request, ParameterBag $params ): void {
		$request->setFirstName( '' )
			->setLastName( '' )
			->setSalutation( '' )
			->setTitle( '' )
			->setCompany( $params->get( 'company', '' ) );
	}

	private function addPostalParams( ChangeAddressRequest $request, ParameterBag $params ): void {
		$request->setAddress( $params->get( 'street', '' ) )
			->setPostcode( $params->get( 'postcode', '' ) )
			->setCity( $params->get( 'city', '' ) )
			->setCountry( $params->get( 'country', 'DE' ) );
	}

	private function addReceiptParams( ChangeAddressRequest $request, ParameterBag $params ) {
		if ( ! $params->get( 'receiptOptOut', '' ) ) {
			$request->setIsOptOutOnly( false );
			$request->setDonationReceipt( false );
			return;
		}
		$request->setDonationReceipt( false );
		$request->setIsOptOutOnly( $this->isOptOutOnly( $params ) );
	}

	private function isOptOutOnly( ParameterBag $params ): bool {
		$requirePostalFields = [ 'street', 'postcode', 'city' ];
		$requiredNameFiels = $params->get( 'addressType', '' ) == 'person' ?
			[ 'firstName', 'lastName' ] : [ 'company' ];
		$requiredFields = array_merge( $requiredNameFiels, $requirePostalFields );
		$allFieldsAreEmpty = true;
		foreach( $requiredFields as $field ) {
			if ( $params->get( $field, '' ) !== '' ) {
				$allFieldsAreEmpty = false;
				break;
			}
		}
		return $allFieldsAreEmpty;
	}

}