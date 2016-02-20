<?php

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;

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
		// TODO: handle exceptions
		$this->entityManager->persist( $this->newDonationEntity( $donation ) );
		$this->entityManager->flush();

		// TODO: return donation id
	}

	private function newDonationEntity( Donation $donation ): DoctrineDonation {
		$doctrineDonation = new DoctrineDonation();
		$doctrineDonation->setStatus( $donation->getInitialStatus() );
		$doctrineDonation->setAmount( $donation->getAmount() );
		$doctrineDonation->setPeriod( $donation->getInterval() );

		$doctrineDonation->setPaymentType( $donation->getPaymentType() );

		if ( $donation->getPaymentType() === PaymentType::BANK_TRANSFER ) {
			$doctrineDonation->setTransferCode( $donation->generateTransferCode() );
		}

		if ( $donation->getPersonalInfo() === null ) {
			$doctrineDonation->setName( 'anonym' );
		} else {
			$doctrineDonation->setCity( $donation->getPersonalInfo()->getPhysicalAddress()->getCity() );
			$doctrineDonation->setEmail( $donation->getPersonalInfo()->getEmailAddress() );
			$doctrineDonation->setName( $donation->determineFullName() );
			$doctrineDonation->setInfo( $donation->getOptIn() );
		}

		// TODO: move the enconding to the entity class in FundraisingStore
		$doctrineDonation->setData( base64_encode( serialize( $this->getDataMap( $donation ) ) ) );

		return $doctrineDonation;
	}

	private function getDataMap( Donation $donation ): array {
		$data = [
			'layout' => $donation->getLayout(),
			'impCount' => $donation->getTotalImpressionCount(),
			'bImpCount' => $donation->getSingleBannerImpressionCount(),
			'tracking' => $donation->getTracking(),
			'skin' => $donation->getSkin(),
			'color' => $donation->getColor(),
			'source' => $donation->getSource(),
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

		if ( $donation->getPersonalInfo() === null ) {
			$data['addresstyp'] = 'anonym';
		}
		else {
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
		}

		return $data;
	}

}