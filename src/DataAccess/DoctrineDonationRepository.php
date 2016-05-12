<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Frontend\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentWithoutAssociatedData;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\Model\TrackingInfo;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineDonationRepository implements DonationRepository {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function storeDonation( Donation $donation ) {
		if ( $donation->getId() === null ) {
			$this->insertDonation( $donation );
		}
		else {
			$this->updateDonation( $donation );
		}
	}

	private function insertDonation( Donation $donation ) {
		$doctrineApplication = new DoctrineDonation();
		$this->updateDonationEntity( $doctrineApplication, $donation );

		try {
			$this->entityManager->persist( $doctrineApplication );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreDonationException( $ex );
		}

		$donation->assignId( $doctrineApplication->getId() );
	}

	private function updateDonation( Donation $donation ) {
		$doctrineDonation = $this->getDoctrineDonationById( $donation->getId() );

		if ( $doctrineDonation === null ) {
			throw new StoreDonationException();
		}

		$this->updateDonationEntity( $doctrineDonation, $donation );

		try {
			$this->entityManager->persist( $doctrineDonation );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreDonationException( $ex );
		}
	}

	private function updateDonationEntity( DoctrineDonation $doctrineDonation, Donation $donation ) {
		$doctrineDonation->setId( $donation->getId() );
		$this->updatePaymentInformation( $doctrineDonation, $donation );
		$this->updateDonorInformation( $doctrineDonation, $donation->getDonor() );
		$doctrineDonation->setDonorOptsIntoNewsletter( $donation->getOptsIntoNewsletter() );

		$doctrineDonation->encodeAndSetData( array_merge(
			$doctrineDonation->getDecodedData(),
			$this->getDataMap( $donation )
		) );
	}

	private function updatePaymentInformation( DoctrineDonation $doctrineDonation, Donation $donation ) {
		$doctrineDonation->setStatus( $donation->getStatus() );
		$doctrineDonation->setAmount( $donation->getAmount()->getEuroString() );
		$doctrineDonation->setPaymentIntervalInMonths( $donation->getPaymentIntervalInMonths() );

		$doctrineDonation->setPaymentType( $donation->getPaymentType() );
		$doctrineDonation->setBankTransferCode( $this->getBankTransferCode( $donation->getPaymentMethod() ) );
	}

	private function updateDonorInformation( DoctrineDonation $doctrineDonation, Donor $donor = null ) {
		if ( $donor === null ) {
			$doctrineDonation->setDonorFullName( 'Anonym' );
		} else {
			$doctrineDonation->setDonorCity( $donor->getPhysicalAddress()->getCity() );
			$doctrineDonation->setDonorEmail( $donor->getEmailAddress() );
			$doctrineDonation->setDonorFullName( $donor->getPersonName()->getFullName() );
		}
	}

	private function getBankTransferCode( PaymentMethod $paymentMethod ): string {
		if ( $paymentMethod instanceof BankTransferPayment ) {
			return $paymentMethod->getBankTransferCode();
		}

		return '';
	}

	private function getDataMap( Donation $donation ): array {
		return array_merge(
			$this->getDataFieldsFromTrackingInfo( $donation->getTrackingInfo() ),
			$this->getDataFieldsForPaymentData( $donation->getPaymentMethod() ),
			$this->getDataFieldsFromDonor( $donation->getDonor() )
		);
	}

	private function getDataFieldsFromTrackingInfo( TrackingInfo $trackingInfo ): array {
		return [
			'layout' => $trackingInfo->getLayout(),
			'impCount' => $trackingInfo->getTotalImpressionCount(),
			'bImpCount' => $trackingInfo->getSingleBannerImpressionCount(),
			'tracking' => $trackingInfo->getTracking(),
			'skin' => $trackingInfo->getSkin(),
			'color' => $trackingInfo->getColor(),
			'source' => $trackingInfo->getSource(),
		];
	}

	private function getDataFieldsForPaymentData( PaymentMethod $paymentMethod ): array {
		if ( $paymentMethod instanceof DirectDebitPayment ) {
			return $this->getDataFieldsFromBankData( $paymentMethod->getBankData() );
		}

		if ( $paymentMethod instanceof PayPalPayment ) {
			return $this->getDataFieldsFromPayPalData( $paymentMethod->getPayPalData() );
		}

		return [];
	}

	private function getDataFieldsFromBankData( BankData $bankData ): array {
		return [
			'iban' => $bankData->getIban()->toString(),
			'bic' => $bankData->getBic(),
			'konto' => $bankData->getAccount(),
			'blz' => $bankData->getBankCode(),
			'bankname' => $bankData->getBankName(),
		];
	}

	private function getDataFieldsFromDonor( Donor $personalInfo = null ): array {
		if ( $personalInfo === null ) {
			return [ 'addresstyp' => 'anonym' ];
		}

		return array_merge(
			$this->getDataFieldsFromPersonName( $personalInfo->getPersonName() ),
			$this->getDataFieldsFromAddress( $personalInfo->getPhysicalAddress() ),
			[ 'email' => $personalInfo->getEmailAddress() ]
		);
	}

	private function getDataFieldsFromPersonName( PersonName $name ) {
		return [
			'adresstyp' => $name->getPersonType(),
			'anrede' => $name->getSalutation(),
			'titel' => $name->getTitle(),
			'vorname' => $name->getFirstName(),
			'nachname' => $name->getLastName(),
			'firma' => $name->getCompanyName(),
		];
	}

	private function getDataFieldsFromAddress( PhysicalAddress $address ) {
		return [
			'strasse' => $address->getStreetAddress(),
			'plz' => $address->getPostalCode(),
			'ort' => $address->getCity(),
			'country' => $address->getCountryCode(),
		];
	}

	private function getDataFieldsFromPayPalData( PayPalData $payPalData ) {
		return [
			'paypal_payer_id' => $payPalData->getPayerId(),
			'paypal_subscr_id' => $payPalData->getSubscriberId(),
			'paypal_payer_status' => $payPalData->getPayerStatus(),
			'paypal_address_status' => $payPalData->getAddressStatus(),
			'paypal_mc_gross' => $payPalData->getAmount()->getEuroString(),
			'paypal_mc_currency' => $payPalData->getCurrencyCode(),
			'paypal_mc_fee' => $payPalData->getFee()->getEuroString(),
			'paypal_settle_amount' => $payPalData->getSettleAmount()->getEuroString(),
			'paypal_first_name' => $payPalData->getFirstName(),
			'paypal_last_name' => $payPalData->getLastName(),
			'paypal_address_name' => $payPalData->getAddressName(),
			'ext_payment_id' => $payPalData->getPaymentId(),
			'ext_subscr_id' => $payPalData->getSubscriberId(),
			'ext_payment_type' => $payPalData->getPaymentType(),
			'ext_payment_status' => $payPalData->getPaymentStatus(),
			'ext_payment_account' => $payPalData->getPayerId(),
			'ext_payment_timestamp' => $payPalData->getPaymentTimestamp()
		];
	}

	/**
	 * @param int $id
	 *
	 * @return Donation|null
	 * @throws GetDonationException
	 */
	public function getDonationById( int $id ) {
		try {
			$donation = $this->getDoctrineDonationById( $id );
		}
		catch ( ORMException $ex ) {
			throw new GetDonationException( $ex );
		}

		if ( $donation === null ) {
			return null;
		}

		return $this->newDonationDomainObject( $donation );
	}

	/**
	 * @param int $id
	 * @return DoctrineDonation|null
	 * @throws ORMException
	 */
	public function getDoctrineDonationById( int $id ) {
		return $this->entityManager->getRepository( DoctrineDonation::class )->findOneBy( [
			'id' => $id,
			'deletionTime' => null
		] );
	}

	private function newDonationDomainObject( DoctrineDonation $dd ): Donation {
		return new Donation(
			$dd->getId(),
			$dd->getStatus(),
			$this->getDonorFromEntity( $dd ),
			$this->getPaymentFromEntity( $dd ),
			(bool)$dd->getDonorOptsIntoNewsletter(),
			$this->getTrackingInfoFromEntity( $dd )
		);
	}

	/**
	 * @param DoctrineDonation $dd
	 *
	 * @return Donor|null
	 */
	private function getDonorFromEntity( DoctrineDonation $dd ) {
		if ( $dd->getDonorEmail() === null ) {
			return null;
		}

		return new Donor(
			$this->getPersonNameFromEntity( $dd ),
			$this->getPhysicalAddressFromEntity( $dd ),
			$dd->getDonorEmail()
		);
	}

	private function getPaymentFromEntity( DoctrineDonation $dd ): DonationPayment {
		return new DonationPayment(
			Euro::newFromString( $dd->getAmount() ),
			$dd->getPaymentIntervalInMonths(),
			$this->getPaymentMethodFromEntity( $dd )
		);
	}

	private function getPaymentMethodFromEntity( DoctrineDonation $dd ): PaymentMethod {
		if ( $dd->getPaymentType() === PaymentType::BANK_TRANSFER ) {
			return new BankTransferPayment( $dd->getBankTransferCode() );
		}

		if ( $dd->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			return new DirectDebitPayment( $this->getBankDataFromEntity( $dd ) );
		}

		if ( $dd->getPaymentType() === PaymentType::PAYPAL ) {
			return new PayPalPayment( $this->getPayPalDataFromEntity( $dd ) );
		}

		return new PaymentWithoutAssociatedData( $dd->getPaymentType() );
	}

	private function getPersonNameFromEntity( DoctrineDonation $dd ): PersonName {
		$data = $dd->getDecodedData();

		$name = $data['adresstyp'] === PersonName::PERSON_COMPANY
			? PersonName::newCompanyName() : PersonName::newPrivatePersonName();

		$name->setSalutation( $data['anrede'] );
		$name->setTitle( $data['titel'] );
		$name->setFirstName( $data['vorname'] );
		$name->setLastName( $data['nachname'] );
		$name->setCompanyName( $data['firma'] );

		return $name->freeze()->assertNoNullFields();
	}

	private function getPhysicalAddressFromEntity( DoctrineDonation $dd ): PhysicalAddress {
		$data = $dd->getDecodedData();

		$address = new PhysicalAddress();

		$address->setStreetAddress( $data['strasse'] );
		$address->setCity( $data['ort'] );
		$address->setPostalCode( $data['plz'] );
		$address->setCountryCode( $data['country'] );

		return $address->freeze()->assertNoNullFields();
	}

	/**
	 * @param DoctrineDonation $dd
	 * @return BankData|null
	 */
	private function getBankDataFromEntity( DoctrineDonation $dd ) {
		$data = $dd->getDecodedData();

		if ( array_key_exists( 'iban', $data ) ) {
			$bankData = new BankData();

			$bankData->setIban( new Iban( $data['iban'] ) );
			$bankData->setBic( $data['bic'] );
			$bankData->setAccount( $data['konto'] );
			$bankData->setBankCode( $data['blz'] );
			$bankData->setBankName( $data['bankname'] );

			return $bankData->freeze()->assertNoNullFields();
		}

		return null;
	}

	private function getTrackingInfoFromEntity( DoctrineDonation $dd ): TrackingInfo {
		$data = $dd->getDecodedData();

		$trackingInfo = new TrackingInfo();

		$trackingInfo->setLayout( $data['layout'] );
		$trackingInfo->setTotalImpressionCount( $data['impCount'] );
		$trackingInfo->setSingleBannerImpressionCount( $data['bImpCount'] );
		$trackingInfo->setTracking( $data['tracking'] );
		$trackingInfo->setSkin( $data['skin'] );
		$trackingInfo->setColor( $data['color'] );
		$trackingInfo->setSource( $data['source'] );

		return $trackingInfo->freeze()->assertNoNullFields();
	}

	/**
	 * @param DoctrineDonation $dd
	 * @return PayPalData|null
	 */
	private function getPayPalDataFromEntity( DoctrineDonation $dd ) {
		$data = $dd->getDecodedData();

		if ( array_key_exists( 'paypal_payer_id', $data ) ) {
			return ( new PayPalData() )
				->setPayerId( $data['paypal_payer_id'] )
				->setSubscriberId( $data['paypal_subscr_id'] )
				->setPayerStatus( $data['paypal_payer_status'] )
				->setAddressStatus( $data['paypal_address_status'] )
				->setAmount( Euro::newFromString( $data['paypal_mc_gross'] ) )
				->setCurrencyCode( $data['paypal_mc_currency'] )
				->setFee( Euro::newFromString( $data['paypal_mc_fee'] ) )
				->setSettleAmount( Euro::newFromString( $data['paypal_settle_amount'] ) )
				->setFirstName( $data['paypal_first_name'] )
				->setLastName( $data['paypal_last_name'] )
				->setAddressName( $data['paypal_address_name'] )
				->setPaymentId( $data['ext_payment_id'] )
				->setPaymentType( $data['ext_payment_type'] )
				->setPaymentStatus( $data['ext_payment_status'] )
				->setPaymentTimestamp( $data['ext_payment_timestamp'] )
				->freeze()->assertNoNullFields();
		}

		return null;
	}

}
