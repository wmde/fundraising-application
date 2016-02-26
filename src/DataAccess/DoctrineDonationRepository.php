<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;
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
		$doctrineDonation = $this->newDonationEntity( $donation );

		try {
			$this->entityManager->persist( $doctrineDonation );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreDonationException( $ex );
		}

		$donation->setId( $doctrineDonation->getId() );
	}

	private function newDonationEntity( Donation $donation ): DoctrineDonation {
		$doctrineDonation = new DoctrineDonation();
		$doctrineDonation->setStatus( $donation->getInitialStatus() );
		$doctrineDonation->setAmount( $donation->getAmount() );
		$doctrineDonation->setPeriod( $donation->getInterval() );

		$doctrineDonation->setPaymentType( $donation->getPaymentType() );
		$doctrineDonation->setTransferCode( $donation->getBankTransferCode() );

		if ( $donation->getPersonalInfo() === null ) {
			$doctrineDonation->setName( 'Anonym' );
		} else {
			$doctrineDonation->setCity( $donation->getPersonalInfo()->getPhysicalAddress()->getCity() );
			$doctrineDonation->setEmail( $donation->getPersonalInfo()->getEmailAddress() );
			$doctrineDonation->setName( $donation->getPersonalInfo()->getPersonName()->getFullName() );
			$doctrineDonation->setInfo( $donation->getOptsIntoNewsletter() );
		}

		// TODO: move the enconding to the entity class in FundraisingStore
		$doctrineDonation->setData( base64_encode( serialize( $this->getDataMap( $donation ) ) ) );

		return $doctrineDonation;
	}

	private function getDataMap( Donation $donation ): array {
		return array_merge(
			$this->getDataFieldsFromTrackingInfo( $donation->getTrackingInfo() ),
			$this->getDataFieldsForBankData( $donation ),
			$this->getDataFieldsFromPersonalInfo( $donation->getPersonalInfo() )
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

	private function getDataFieldsFromBankData( BankData $bankData ): array {
		return [
			'iban' => $bankData->getIban()->toString(),
			'bic' => $bankData->getBic(),
			'konto' => $bankData->getAccount(),
			'blz' => $bankData->getBankCode(),
			'bankname' => $bankData->getBankName(),
		];
	}

	private function getDataFieldsForBankData( Donation $donation ): array {
		if ( $donation->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			return $this->getDataFieldsFromBankData( $donation->getBankData() );
		}

		return [];
	}

	private function getDataFieldsFromPersonalInfo( PersonalInfo $personalInfo = null ): array {
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

	/**
	 * @param int $id
	 *
	 * @return Donation|null
	 * @throws GetDonationException
	 */
	public function getDonationById( int $id ) {
		// TODO: dt_gruen should be null

		try {
			/**
			 * @var DoctrineDonation $donation
			 */
			$donation = $this->entityManager->find( DoctrineDonation::class, $id );
		}
		catch ( ORMException $ex ) {
			throw new GetDonationException( $ex );
		}

		if ( $donation === null ) {
			return null;
		}

		return $this->newDonationDomainObject( $donation );
	}

	private function newDonationDomainObject( DoctrineDonation $dd ): Donation {
		$donation = new Donation();

		$donation->setId( $dd->getId() );
		$donation->setStatus( $dd->getStatus() );
		$donation->setAmount( $dd->getAmount() );
		$donation->setInterval( $dd->getPeriod() );
		$donation->setPaymentType( $dd->getPaymentType() );
		$donation->setBankTransferCode( $dd->getTransferCode() );
		$donation->setOptsIntoNewsletter( $dd->getInfo() );

		$donation->setPersonalInfo( $this->getPersonalInfoFromEntity( $dd ) );
		$donation->setBankData( $this->getBankDataFromEntity( $dd ) );
		$donation->setTrackingInfo( $this->getTrackingInfoFromEntity( $dd ) );

		return $donation;
	}

	private function getPersonalInfoFromEntity( DoctrineDonation $dd ): PersonalInfo {
		$personalInfo = new PersonalInfo();

		$personalInfo->setEmailAddress( $dd->getEmail() );
		$personalInfo->setPersonName( $this->getPersonNameFromEntity( $dd ) );
		$this->hitThePersonalInfoWithLeadPipeUntilItFinallySetsTheAddress( $personalInfo, $dd );

		return $personalInfo->freeze()->assertNoNullFields();
	}

	private function hitThePersonalInfoWithLeadPipeUntilItFinallySetsTheAddress(
		PersonalInfo $personalInfo, DoctrineDonation $dd ) {

		// TODO: we might want to use a loop here
		$personalInfo->setPhysicalAddress( $this->getPhysicalAddressFromEntity( $dd ) );
		$personalInfo->setPhysicalAddress( $this->getPhysicalAddressFromEntity( $dd ) );
		$personalInfo->setPhysicalAddress( $this->getPhysicalAddressFromEntity( $dd ) );
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

	private function getBankDataFromEntity( DoctrineDonation $dd ): BankData {
		$data = $dd->getDecodedData();

		$bankData = new BankData();

		$bankData->setIban( new Iban( $data['iban'] ) );
		$bankData->setBic( $data['bic'] );
		$bankData->setAccount( $data['konto'] );
		$bankData->setBankCode( $data['blz'] );
		$bankData->setBankName( $data['bankname'] );

		return $bankData->freeze()->assertNoNullFields();
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

}
