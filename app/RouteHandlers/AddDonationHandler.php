<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Presentation\SelectedConfirmationPage;
use WMDE\Fundraising\Frontend\Infrastructure\AmountParser;
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
		$addDonationRequest = $this->createDonationRequest( $request );
		$responseModel = $this->ffFactory->newAddDonationUseCase()->addDonation( $addDonationRequest );

		if ( !$responseModel->isSuccessful() ) {
			return new Response( $this->ffFactory->newDonationFormViolationPresenter()->present( $responseModel->getValidationErrors(), $addDonationRequest ) );
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
						$responseModel->getDonation()->getPaymentIntervalInMonths(),
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

		$donationRequest->setAmount( $this->getEuroAmountFromString( $request->get( 'betrag', '' ) ) );

		$donationRequest->setPaymentType( $request->get( 'zahlweise', '' ) );
		$donationRequest->setInterval( intval( $request->get( 'periode', 0 ) ) );

		$donationRequest->setPersonalInfo(
			$request->get( 'addressType', '' ) === 'anonym' ? null :  $this->getPersonalInfoFromRequest( $request )
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

	private function getEuroAmountFromString( string $amount ) {
		$locale = 'de_DE'; // TODO: make this configurable for multilanguage support

		return Euro::newFromFloat( ( new AmountParser( $locale ) )->parseAsFloat( $amount ) );
	}

	private function getPersonalInfoFromRequest( Request $request ): Donor {
		return new Donor(
			$this->getNameFromRequest( $request ),
			$this->getPhysicalAddressFromRequest( $request ),
			$request->get( 'email', '' )
		);
	}

	private function getPhysicalAddressFromRequest( Request $request ): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setStreetAddress( $request->get( 'street', '' ) );
		$address->setPostalCode( $request->get( 'postcode', '' ) );
		$address->setCity( $request->get( 'city', '' ) );
		$address->setCountryCode( $request->get( 'country', '' ) );

		return $address->freeze()->assertNoNullFields();
	}

	private function getNameFromRequest( Request $request ): PersonName {
		$name = $request->get( 'addressType', '' ) === PersonName::PERSON_COMPANY
			? PersonName::newCompanyName() : PersonName::newPrivatePersonName();

		$name->setSalutation( $request->get( 'salutation', '' ) );
		$name->setTitle( $request->get( 'title', '' ) );
		$name->setCompanyName( $request->get( 'company', '' ) );
		$name->setFirstName( $request->get( 'firstName', '' ) );
		$name->setLastName( $request->get( 'lastName', '' ) );

		return $name->freeze()->assertNoNullFields();
	}

}