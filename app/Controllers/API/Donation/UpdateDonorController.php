<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\API\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorType;
use WMDE\Fundraising\DonationContext\UseCases\GetDonation\GetDonationRequest;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;
use WMDE\Fundraising\Frontend\Presentation\DonorDataFormatter;

class UpdateDonorController extends AbstractApiController {

	private const MESSAGE_EMPTY_BODY = 'update_donor_empty_request_body';
	private const MESSAGE_FAILED = 'update_donor_failed';

	public function index( Request $request, FunFunFactory $ffFactory, string $accessToken ): Response {
		$data = new ParameterBag( $request->toArray() );

		if ( $data->count() === 0 ) {
			return $this->errorResponse( self::MESSAGE_EMPTY_BODY, Response::HTTP_BAD_REQUEST );
		}

		$responseModel = $ffFactory
			->newUpdateDonorUseCase( $data->get( 'updateToken', '' ), $accessToken )
			->updateDonor( $this->newRequestModel( $data ) );

		if ( !$responseModel->isSuccessful() ) {
			return $this->errorResponse( self::MESSAGE_FAILED, Response::HTTP_BAD_REQUEST, [ $responseModel->getErrorMessage() ] );
		}

		$useCase = $ffFactory->newGetDonationUseCase( $accessToken );
		$donationResponse = $useCase->showConfirmation( new GetDonationRequest( $data->getInt( 'donationId' ) ) );
		$donation = $donationResponse->getDonation();

		return new JsonResponse( array_merge(
			[
				'addressType' => AddressType::donorToPresentationAddressType( $donation->getDonor() ),
				'mailingList' => $donation->getDonor()->isSubscribedToMailingList()
			],
			( new DonorDataFormatter() )->getAddressArguments( $donation )
		) );
	}

	private function newRequestModel( ParameterBag $params ): UpdateDonorRequest {
		$request = UpdateDonorRequest::newInstance()
			->withType(
				$this->getAddressType( $params )
			)
			->withDonationId( intval( $params->get( 'donationId', '' ) ) )
			->withCity( $params->get( 'city', '' ) )
			->withCompanyName( $params->get( 'companyName', '' ) )
			->withCountryCode( $params->get( 'country', '' ) )
			->withEmailAddress( $params->get( 'email', '' ) )
			->withFirstName( $params->get( 'firstName', '' ) )
			->withLastName( $params->get( 'lastName', '' ) )
			->withPostalCode( $params->get( 'postcode', '' ) )
			->withSalutation( $params->get( 'salutation', '' ) )
			->withStreetAddress( $params->get( 'street', '' ) )
			->withTitle( $params->get( 'title', '' ) );

		if ( $params->get( 'mailingList', false ) ) {
			return $request->acceptMailingList();
		} else {
			return $request->declineMailingList();
		}
	}

	/**
	 * Get UpdateDonorRequest donor type from HTTP request field.
	 *
	 * Assumes "Anonymous" when field is not set or invalid.
	 *
	 * @param ParameterBag $params
	 *
	 * @return DonorType
	 */
	private function getAddressType( ParameterBag $params ): DonorType {
		try {
			return DonorType::make(
				AddressType::presentationAddressTypeToDomainAddressType( $params->get( 'addressType', '' ) )
			);
		} catch ( \UnexpectedValueException $e ) {
			return DonorType::ANONYMOUS();
		}
	}
}
