<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Domain\SelectedConfirmationPage;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationRequest;
use WMDE\Fundraising\Frontend\UseCases\AddDonation\AddDonationResponse;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationHandler {

	private $ffFactory;
	private $app;

	public function __construct( FunFunFactory $ffFactory, Application $app ) {
		$this->ffFactory = $ffFactory;
		$this->app = $app;
	}

	public function handle( Request $request ): Response {
		$responseModel = $this->ffFactory->newAddDonationUseCase()->addDonation(
			$this->createDonationRequest( $request )
		);

		if ( !$responseModel->isSuccessful() ) {
			return new Response( 'TODO: error occurred' ); // TODO
		}

		return $this->newHttpResponse( $responseModel, $this->ffFactory->getDonationConfirmationPageSelector()->selectPage() );
	}

	private function newHttpResponse( AddDonationResponse $responseModel, SelectedConfirmationPage $selectedPage ): Response {
		switch( $responseModel->getDonation()->getPaymentType() ) {
			case PaymentType::DIRECT_DEBIT:
			case PaymentType::BANK_TRANSFER:
				return new Response( $this->ffFactory->newDonationConfirmationPresenter()->present(
					$responseModel->getDonation(),
					$responseModel->getUpdateToken(),
					$selectedPage
				) );
			case PaymentType::PAYPAL:
				return $this->app->redirect(
					$this->ffFactory->newPayPalUrlGenerator()->generateUrl(
						$responseModel->getDonation()->getId(),
						$responseModel->getDonation()->getAmount(),
						$responseModel->getDonation()->getInterval(),
						$responseModel->getUpdateToken()
						// TODO: include access token
					)
				);
			case PaymentType::CREDIT_CARD:
				return new Response(
					$this->ffFactory->newCreditCardPaymentHtmlPresenter()->present( $responseModel )
				);
			default:
				throw new \LogicException( 'This code should not be reached' );
		}
	}

	private function createDonationRequest( Request $request ): AddDonationRequest {
		$donationRequest = new AddDonationRequest();
		$locale = 'de_DE'; // TODO: make this configurable for multilanguage support
		$donationRequest->setAmountFromString( $request->get( 'betrag', '' ), $locale );
		$donationRequest->setPaymentType( $request->get( 'zahlweise', '' ) );
		$donationRequest->setInterval( intval( $request->get( 'periode', 0 ) ) );

		$donationRequest->setPersonalInfo(
			$request->get( 'adresstyp', '' ) === 'anonym' ? null :  $this->getPersonalInfoFromRequest( $request )
		);

		$donationRequest->setIban( $request->get( 'iban', '' ) );
		$donationRequest->setBic( $request->get( 'bic', '' ) );
		$donationRequest->setBankAccount( $request->get( 'konto', '' ) );
		$donationRequest->setBankCode( $request->get( 'blz', '' ) );
		$donationRequest->setBankName( $request->get( 'bankname', '' ) );

		$donationRequest->setTracking(
			AddDonationRequest::getPreferredValue( [
				$request->cookies->get( 'spenden_tracking' ),
				$request->request->get( 'tracking' ),
				AddDonationRequest::concatTrackingFromVarCouple(
					$request->get( 'piwik_campaign', '' ),
					$request->get( 'piwik_kwd', '' )
				)
			] )
		);

		$donationRequest->setOptIn( $request->get( 'info', '' ) );
		$donationRequest->setSource(
			AddDonationRequest::getPreferredValue( [
				$request->cookies->get( 'spenden_source' ),
				$request->request->get( 'source' ),
				$request->server->get( 'HTTP_REFERER' )
			] )
		);
		$donationRequest->setTotalImpressionCount( intval( $request->get( 'impCount', 0 ) ) );
		$donationRequest->setSingleBannerImpressionCount( intval( $request->get( 'bImpCount', 0 ) ) );
		$donationRequest->setColor( $request->get( 'color', '' ) );
		$donationRequest->setSkin( $request->get( 'skin', '' ) );
		$donationRequest->setLayout( $request->get( 'layout', '' ) );

		return $donationRequest;
	}

	private function getPersonalInfoFromRequest( Request $request ): PersonalInfo {
		return new PersonalInfo(
			$this->getNameFromRequest( $request ),
			$this->getPhysicalAddressFromRequest( $request ),
			$request->get( 'email', '' )
		);
	}

	private function getPhysicalAddressFromRequest( Request $request ): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setStreetAddress( $request->get( 'strasse', '' ) );
		$address->setPostalCode( $request->get( 'plz', '' ) );
		$address->setCity( $request->get( 'ort', '' ) );
		$address->setCountryCode( $request->get( 'country', '' ) );

		return $address->freeze()->assertNoNullFields();
	}

	private function getNameFromRequest( Request $request ): PersonName {
		$name = $request->get( 'adresstyp', '' ) === 'firma'
			? PersonName::newCompanyName() : PersonName::newPrivatePersonName();

		$name->setSalutation( $request->get( 'anrede', '' ) );
		$name->setTitle( $request->get( 'titel', '' ) );
		$name->setCompanyName( $request->get( 'firma', '' ) );
		$name->setFirstName( $request->get( 'vorname', '' ) );
		$name->setLastName( $request->get( 'nachname', '' ) );

		return $name->freeze()->assertNoNullFields();
	}

}