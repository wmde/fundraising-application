<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\API\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressType;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class AddressChangeController extends AbstractApiController {

	public const ADDRESS_TYPE_PERSON = 'person';
	private const MESSAGE_TOKEN_NOT_FOUND = 'address_change_token_not_found';
	private const MESSAGE_EMPTY_BODY = 'address_change_empty_request_body';
	private const MESSAGE_FAILED = 'address_change_failed';

	public function show( Request $request, FunFunFactory $ffFactory, string $identifier, ?string $previousIdentifier ): Response {
		$readAddressChangeUseCase = $ffFactory->newReadAddressChangeUseCase();
		$addressChange = $readAddressChangeUseCase->getAddressChangeByUuids( $identifier, $previousIdentifier ?: $identifier );
		if ( !$addressChange ) {
			$ffFactory->getLogger()->notice( 'Address change record not found', [ 'addressChangeToken' => $identifier ] );
			return $this->errorResponse( self::MESSAGE_TOKEN_NOT_FOUND, Response::HTTP_NOT_FOUND );
		}

		return new JsonResponse( $addressChange );
	}

	public function update( Request $request, FunFunFactory $ffFactory, string $identifier ): Response {
		$data = $request->toArray();

		if ( $data === [] ) {
			return $this->errorResponse( self::MESSAGE_EMPTY_BODY, Response::HTTP_BAD_REQUEST );
		}

		$addressChangeRequest = $this->newAddressChangeRequestFromParams( $identifier, new ParameterBag( $data ) );

		$useCase = $ffFactory->newChangeAddressUseCase();
		$response = $useCase->changeAddress( $addressChangeRequest );

		if ( !$response->isSuccess() ) {
			$ffFactory->getLogger()->error( 'Address change failed', [ 'domain_errors' => $response->getErrors() ] );
			return $this->errorResponse( self::MESSAGE_FAILED, Response::HTTP_BAD_REQUEST, $response->getErrors() );
		}

		$readAddressChangeUseCase = $ffFactory->newReadAddressChangeUseCase();
		$addressChange = $readAddressChangeUseCase->getAddressChangeByUuids( $identifier, $identifier );

		return new JsonResponse( $addressChange );
	}

	private function newAddressChangeRequestFromParams( string $identifier, ParameterBag $params ): ChangeAddressRequest {
		$addressType = $params->get( 'addressType', '' ) === self::ADDRESS_TYPE_PERSON ? AddressType::Person : AddressType::Company;
		$receiptOptOut = $params->get( 'receiptOptOut', false );
		$isOptOutOnly = $receiptOptOut && $this->areAllOptOutOnlyFieldsEmpty( $addressType, $params );

		if ( $addressType == AddressType::Person ) {
			return ChangeAddressRequest::newPersonalChangeAddressRequest(
				salutation: $params->get( 'salutation', '' ),
				title: $params->get( 'title', '' ),
				firstName: $params->get( 'firstName', '' ),
				lastName: $params->get( 'lastName', '' ),
				address: $params->get( 'street', '' ),
				postcode: $params->get( 'postcode', '' ),
				city: $params->get( 'city', '' ),
				country: $params->get( 'country', 'DE' ),
				identifier: $identifier,
				donationReceipt: !$receiptOptOut,
				isOptOutOnly: $isOptOutOnly,
			);
		} else {
			return ChangeAddressRequest::newCompanyChangeAddressRequest(
				company: $params->get( 'company', '' ),
				address: $params->get( 'street', '' ),
				postcode: $params->get( 'postcode', '' ),
				city: $params->get( 'city', '' ),
				country: $params->get( 'country', 'DE' ),
				identifier: $identifier,
				donationReceipt: !$receiptOptOut,
				isOptOutOnly: $isOptOutOnly,
			);
		}
	}

	private function areAllOptOutOnlyFieldsEmpty( AddressType $addressType, ParameterBag $params ): bool {
		$requiredFields = array_merge(
			[ 'street', 'postcode', 'city' ],
			$addressType === AddressType::Person ? [ 'firstName', 'lastName' ] : [ 'company' ]
		);

		foreach ( $requiredFields as $field ) {
			if ( $params->get( $field, '' ) !== '' ) {
				return false;
			}
		}
		return true;
	}
}
