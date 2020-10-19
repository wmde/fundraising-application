<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use WMDE\Fundraising\DonationContext\Domain\Model\DonorType;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorRequest;
use WMDE\Fundraising\DonationContext\UseCases\UpdateDonor\UpdateDonorResponse;
use WMDE\Fundraising\Frontend\App\AccessDeniedException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\AddressType;

/**
 * @license GPL-2.0-or-later
 */
class UpdateDonorController {

	public const ADDRESS_CHANGE_SESSION_KEY = 'address_changed';

	public function index( Request $request, FunFunFactory $ffFactory, SessionInterface $session ): Response {
		$updateToken = $request->request->get( 'updateToken', '' );
		$accessToken = $request->query->get( 'accessToken', '' );
		$responseModel = $ffFactory
			->newUpdateDonorUseCase( $updateToken, $accessToken )
			->updateDonor( $this->newRequestModel( $request ) );
		if ( $request->getAcceptableContentTypes()[0] === 'application/json' ) {
			return $this->createJsonResponse( $responseModel );
		}
		return $this->createHtmlResponse( $session, $ffFactory, $responseModel, $updateToken, $accessToken );
	}

	private function createJsonResponse( UpdateDonorResponse $responseModel ): JsonResponse {
		return JsonResponse::create( [
			'state' => $responseModel->getDonation() !== null || $responseModel->isSuccessful() ? 'OK' : 'ERR',
			'message' => $responseModel->getErrorMessage()
		] );
	}

	private function createHtmlResponse(
		SessionInterface $session,
		FunFunFactory $ffFactory,
		UpdateDonorResponse $responseModel,
		string $updateToken,
		string $accessToken
	): Response {
		if ( $responseModel->getDonation() === null ) {
			throw new AccessDeniedException();
		}
		if ( $responseModel->isSuccessful() ) {
			$session->set(
				self::ADDRESS_CHANGE_SESSION_KEY,
				true
			);
			return new RedirectResponse(
				$ffFactory->getUrlGenerator()->generateAbsoluteUrl(
					'show-donation-confirmation',
					[
						'id' => $responseModel->getDonation()->getId(),
						'accessToken' => $accessToken
					]
				)
			);
		}
		return new Response(
			$ffFactory->newDonorUpdatePresenter()->present(
				$responseModel,
				$responseModel->getDonation(),
				$updateToken,
				$accessToken
			)
		);
	}

	private function newRequestModel( Request $request ): UpdateDonorRequest {
		return UpdateDonorRequest::newInstance()
			->withType(
				$this->getAddressType( $request )
			)
			->withDonationId( intval( $request->get( 'donation_id', '' ) ) )
			->withCity( $request->get( 'city', '' ) )
			->withCompanyName( $request->get( 'companyName', '' ) )
			->withCountryCode( $request->get( 'country', '' ) )
			->withEmailAddress( $request->get( 'email', '' ) )
			->withFirstName( $request->get( 'firstName', '' ) )
			->withLastName( $request->get( 'lastName', '' ) )
			->withPostalCode( $request->get( 'postcode', '' ) )
			->withSalutation( $request->get( 'salutation', '' ) )
			->withStreetAddress( $request->get( 'street', '' ) )
			->withTitle( $request->get( 'title', '' ) );
	}

	/**
	 * Get UpdateDonorRequest donor type from HTTP request field.
	 *
	 * Assumes "Anonymous" when field is not set or invalid.
	 *
	 * @param Request $request
	 *
	 * @return DonorType
	 */
	private function getAddressType( Request $request ): DonorType {
		try {
			return DonorType::make(
				AddressType::presentationAddressTypeToDomainAddressType( $request->get( 'addressType', '' ) )
			);
		} catch ( \UnexpectedValueException $e ) {
			return DonorType::ANONYMOUS();
		}
	}
}
