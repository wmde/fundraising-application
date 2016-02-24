<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\Model\TrackingInfo;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
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
		try {
			$this->entityManager->persist( $this->newDonationEntity( $donation ) );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreDonationException( $ex );
		}

		// TODO: return donation id
	}

	private function newDonationEntity( Donation $donation ): DoctrineDonation {
		$doctrineDonation = new DoctrineDonation();
		$doctrineDonation->setStatus( $donation->getInitialStatus() );
		$doctrineDonation->setAmount( $donation->getAmount() );
		$doctrineDonation->setPeriod( $donation->getInterval() );

		$doctrineDonation->setPaymentType( $donation->getPaymentType() );
		$doctrineDonation->setTransferCode( $donation->getBankTransferCode() );

		if ( $donation->getPersonalInfo() === null ) {
			$doctrineDonation->setName( 'anonym' );
		} else {
			$doctrineDonation->setCity( $donation->getPersonalInfo()->getPhysicalAddress()->getCity() );
			$doctrineDonation->setEmail( $donation->getPersonalInfo()->getEmailAddress() );
			$doctrineDonation->setName( $donation->determineFullName() );
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

}