<?php

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Domain\BankData;
use WMDE\Fundraising\Frontend\Domain\Donation;
use WMDE\Fundraising\Frontend\Domain\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\GeneralizedReferrer;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationUseCase {

	private $donationRepository;
	private $donationValidator;
	private $generalizedReferrer;

	public function __construct( DonationRepository $donationRepository, DonationValidator $donationValidator,
								 GeneralizedReferrer $generalizedReferrer ) {
		$this->donationRepository = $donationRepository;
		$this->donationValidator = $donationValidator;
		$this->generalizedReferrer = $generalizedReferrer;
	}

	public function addDonation( AddDonationRequest $donationRequest ) {
		$donation = new Donation();

		$donation->setAmount( $donationRequest->getAmount() );
		$donation->setInterval( $donationRequest->getInterval() );
		$donation->setPersonalInfo( $donationRequest->getPersonalInfo() );
		$donation->setOptIn( $donationRequest->getOptIn() );
		$donation->setPaymentType( $donationRequest->getPaymentType() );
		$donation->setTracking( $donationRequest->getTracking() );
		$donation->setSource( $donationRequest->getSource() );
		$donation->setTotalImpressionCount( $donationRequest->getTotalImpressionCount() );
		$donation->setSingleBannerImpressionCount( $donationRequest->getSingleBannerImpressionCount() );
		$donation->setColor( $donationRequest->getColor() );
		$donation->setSkin( $donationRequest->getSkin() );
		$donation->setLayout( $donationRequest->getLayout() );

		// TODO: try to complement bank data if some fields are missing
		if ( $donationRequest->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			$donation->setBankData( $this->newBankDataFromRequest( $donationRequest ) );
		}

		$validationResult = $this->donationValidator->validate( $donation );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		$this->donationRepository->storeDonation( $this->newDonationEntityFromDonationDomain( $donation ) );

		// TODO: send mails

		return ValidationResponse::newSuccessResponse();
	}

	private function newBankDataFromRequest( AddDonationRequest $request ): BankData {
		$bankData = new BankData();
		$bankData->setIban( new Iban( $request->getIban() ) )
			->setBic( $request->getBic() )
			->setAccount( $request->getBankAccount() )
			->setBankCode( $request->getBankCode() )
			->setBankName( $request->getBankName() );
		return $bankData->freeze()->assertNoNullFields();
	}

	private function newDonationEntityFromDonationDomain( Donation $donation ) {
		$dbalDonation = new \WMDE\Fundraising\Entities\Donation();
		$dbalDonation->setStatus( $donation->getInitialStatus() );
		$dbalDonation->setAmount( $donation->getAmount() );
		$dbalDonation->setPeriod( $donation->getInterval() );

		$dbalDonation->setPaymentType( $donation->getPaymentType() );
		if ( $donation->getPaymentType() === PaymentType::BANK_TRANSFER ) {
			// TODO: generate transfer code
			$dbalDonation->setTransferCode( '' );
		}

		$data = [
			'addresstyp' => 'anonym',
			'layout' => $donation->getLayout(),
			'impCount' => $donation->getTotalImpressionCount(),
			'bImpCount' => $donation->getSingleBannerImpressionCount(),
			'tracking' => $donation->getTracking(),
			'skin' => $donation->getSkin(),
			'color' => $donation->getColor(),
			// TODO: use the GeneralizedReferrer to determine
			'source' => $this->generalizedReferrer->generalize( $donation->getSource() ),
		];

		if ( $donation->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			$data = array_merge( $data, [
				'iban' => $donation->getBankData()->getIban()->toString(),
				'bic' => $donation->getBankData()->getBic(),
				'konto' => $donation->getBankData()->getAccount(),
				'blz' => $donation->getBankData()->getBankCode(),
				'bankname' => $donation->getBankData()->getBankName(),
			] );
		}

		if ( $donation->getPersonalInfo() !== null ) {
			$data = array_merge( $data, [
				'adresstyp' => $donation->getPersonalInfo()->getPersonName()->getPersonType(),
				'anrede' => $donation->getPersonalInfo()->getPersonName()->getSalutation(),
				'titel' => $donation->getPersonalInfo()->getPersonName()->getTitle(),
				'vorname' => $donation->getPersonalInfo()->getPersonName()->getFirstName(),
				'nachname' => $donation->getPersonalInfo()->getPersonName()->getLastName(),
				'firma' => $donation->getPersonalInfo()->getPersonName()->getCompanyName(),
				'strasse' => $donation->getPersonalInfo()->getPhysicalAddress()->getStreetAddress(),
				'plz' => $donation->getPersonalInfo()->getPhysicalAddress()->getPostalCode(),
				'ort' => $donation->getPersonalInfo()->getPhysicalAddress()->getCity(),
				'country' => $donation->getPersonalInfo()->getPhysicalAddress()->getCountryCode(),
				'email' => $donation->getPersonalInfo()->getEmailAddress(),
			] );
			$dbalDonation->setCity( $donation->getPersonalInfo()->getPhysicalAddress()->getCity() );
			$dbalDonation->setEmail( $donation->getPersonalInfo()->getEmailAddress() );
			// TODO: generate full name string from name parts (title, first name, last name, company)
			$dbalDonation->setName( '' );
			$dbalDonation->setInfo( $donation->getOptIn() );
		} else {
			$dbalDonation->setName( 'anonym' );
		}

		// TODO: move the enconding to the entity class in FundraisingStore
		$dbalDonation->setData( base64_encode( serialize( $data ) ) );

		return $dbalDonation;
	}

}