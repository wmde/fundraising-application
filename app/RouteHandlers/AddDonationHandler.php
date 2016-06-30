<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
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
		if ( !$this->isSubmissionAllowed() ) {
			return new Response( $this->ffFactory->newSystemMessageResponse( 'donation_rejected_limit' ) );
		}

		$addDonationRequest = $this->createDonationRequest( $request );
		$responseModel = $this->ffFactory->newAddDonationUseCase()->addDonation( $addDonationRequest );

		if ( !$responseModel->isSuccessful() ) {
			return new Response( $this->ffFactory->newDonationFormViolationPresenter()->present( $responseModel->getValidationErrors(), $addDonationRequest ) );
		}

		$this->ffFactory->getCookieHandler()->setCookie( 'donation_timestamp', ( new \DateTime() )->format( 'Y-m-d H:i:s' ) );
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
						$responseModel->getUpdateToken(),
						$responseModel->getAccessToken()
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

		$donationRequest->setDonorType( $request->get( 'addressType', '' ) );
		$donationRequest->setDonorSalutation( $request->get( 'salutation', '' ) );
		$donationRequest->setDonorTitle( $request->get( 'title', '' ) );
		$donationRequest->setDonorCompany( $request->get( 'company', '' ) );
		$donationRequest->setDonorFirstName( $request->get( 'firstName', '' ) );
		$donationRequest->setDonorLastName( $request->get( 'lastName', '' ) );
		$donationRequest->setDonorStreetAddress( $request->get( 'street', '' ) );
		$donationRequest->setDonorPostalCode( $request->get( 'postcode', '' ) );
		$donationRequest->setDonorCity( $request->get( 'city', '' ) );
		$donationRequest->setDonorCountryCode( $request->get( 'country', '' ) );
		$donationRequest->setDonorEmailAddress( $request->get( 'email', '' ) );

		if ( $request->get( 'zahlweise', '' ) === PaymentType::DIRECT_DEBIT ) {
			$donationRequest->setBankData( $this->getBankDataFromRequest( $request ) );
		}

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

	private function getBankDataFromRequest( Request $request ): BankData {
		$bankData = new BankData();
		$bankData->setIban( new Iban( $request->get( 'iban', '' ) ) )
			->setBic( $request->get( 'bic', '' ) )
			->setAccount( $request->get( 'konto', '' ) )
			->setBankCode( $request->get( 'blz', '' ) )
			->setBankName( $request->get( 'bankname', '' ) );

		if ( $bankData->hasIban() && !$bankData->hasCompleteLegacyBankData() ) {
			$bankData = $this->newBankDataFromIban( $bankData->getIban() );
		}
		if ( $bankData->hasCompleteLegacyBankData() && !$bankData->hasIban() ) {
			$bankData = $this->newBankDataFromAccountAndBankCode( $bankData->getAccount(), $bankData->getBankCode() );
		}

		return $bankData->freeze()->assertNoNullFields();
	}

	private function newBankDataFromIban( Iban $iban ): BankData {
		return $this->ffFactory->newBankDataConverter()->getBankDataFromIban( $iban );
	}

	private function newBankDataFromAccountAndBankCode( string $account, string $bankCode ): BankData {
		return $this->ffFactory->newBankDataConverter()->getBankDataFromAccountData( $account, $bankCode );
	}

	private function getEuroAmountFromString( string $amount ) {
		$locale = 'de_DE'; // TODO: make this configurable for multilanguage support

		return Euro::newFromFloat( ( new AmountParser( $locale ) )->parseAsFloat( $amount ) );
	}

	private function isSubmissionAllowed() {
		$donationTimestamp = $this->ffFactory->getCookieHandler()->getCookie( 'donation_timestamp' );

		if ( $donationTimestamp !== '' ) {
			$minNextTimestamp = \DateTime::createFromFormat( 'Y-m-d H:i:s', $donationTimestamp )
				->add( new \DateInterval( $this->ffFactory->getDonationTimeframeLimit() ) );
			if ( $minNextTimestamp > new \DateTime() ) {
				return false;
			}
		}

		return true;
	}

}