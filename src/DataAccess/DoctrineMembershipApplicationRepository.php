<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineApplication;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplicant;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipPayment;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhoneNumber;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreMembershipApplicationException;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineMembershipApplicationRepository implements MembershipApplicationRepository {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function storeApplication( MembershipApplication $application ) {
		if ( $application->hasId() ) {
			$this->updateApplication( $application );
		}
		else {
			$this->insertApplication( $application );
		}
	}

	private function insertApplication( MembershipApplication $application ) {
		$doctrineApplication = new DoctrineApplication();
		$this->updateDoctrineApplication( $doctrineApplication, $application );

		try {
			$this->entityManager->persist( $doctrineApplication );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreMembershipApplicationException( $ex );
		}

		$application->assignId( $doctrineApplication->getId() );
	}

	private function updateApplication( MembershipApplication $application ) {
		$doctrineApplication = $this->getDoctrineApplicationById( $application->getId() );

		if ( $doctrineApplication === null ) {
			throw new StoreMembershipApplicationException();
		}

		$this->updateDoctrineApplication( $doctrineApplication, $application );

		try {
			$this->entityManager->persist( $doctrineApplication );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreMembershipApplicationException( $ex );
		}
	}

	private function updateDoctrineApplication( DoctrineApplication $doctrineApplication, MembershipApplication $application ) {
		$doctrineApplication->setId( $application->getId() );
		$doctrineApplication->setMembershipType( $application->getType() );

		$this->setApplicantFields( $doctrineApplication, $application->getApplicant() );
		$this->setPaymentFields( $doctrineApplication, $application->getPayment() );

		$doctrineApplication->encodeAndSetData( array_merge(
			$doctrineApplication->getDecodedData(),
			$this->getDataMap( $application )
		) );
	}

	private function setApplicantFields( DoctrineApplication $application, MembershipApplicant $applicant ) {
		$application->setApplicantFirstName( $applicant->getPersonName()->getFirstName() );
		$application->setApplicantLastName( $applicant->getPersonName()->getLastName() );
		$application->setApplicantSalutation( $applicant->getPersonName()->getSalutation() );
		$application->setApplicantTitle( $applicant->getPersonName()->getTitle() );

		$application->setApplicantDateOfBirth( $applicant->getDateOfBirth() );

		$application->setApplicantEmailAddress( $applicant->getEmailAddress()->getFullAddress() );
		$application->setApplicantPhoneNumber( $applicant->getPhoneNumber()->__toString() );

		$address = $applicant->getPhysicalAddress();

		$application->setCity( $address->getCity() );
		$application->setCountry( $address->getCountryCode() );
		$application->setPostcode( $address->getPostalCode() );
		$application->setAddress( $address->getStreetAddress() );
	}

	private function setPaymentFields( DoctrineApplication $application, MembershipPayment $payment ) {
		$application->setPaymentIntervalInMonths( $payment->getIntervalInMonths() );
		$application->setPaymentAmount( (int)$payment->getAmount()->getEuroFloat() );

		$bankData = $payment->getBankData();

		$application->setPaymentBankAccount( $bankData->getAccount() );
		$application->setPaymentBankCode( $bankData->getBankCode() );
		$application->setPaymentBankName( $bankData->getBankName() );
		$application->setPaymentBic( $bankData->getBic() );
		$application->setPaymentIban( $bankData->getIban()->toString() );
	}

	private function getDataMap( MembershipApplication $application ): array {
		return array_merge(
			$this->getDataFieldsFromApplicant( $application->getApplicant() ),
			$this->getDataFieldsFromBankData( $application->getPayment()->getBankData() )
		);
	}

	private function getDataFieldsFromBankData( BankData $bankData ): array {
		return [
			'iban' => $bankData->getIban()->toString(),
			'bic' => $bankData->getBic(),
			'account_number' => $bankData->getAccount(),
			'bank_code' => $bankData->getBankCode(),
			'bank_name' => $bankData->getBankName(),
		];
	}

	private function getDataFieldsFromApplicant( MembershipApplicant $applicant ): array {
		return array_merge(
			$this->getDataFieldsFromPersonName( $applicant->getPersonName() ),
			$this->getDataFieldsFromAddress( $applicant->getPhysicalAddress() ),
			[
				'email' => (string)$applicant->getEmailAddress(),
				'phone' => (string)$applicant->getPhoneNumber(),
				'dob' => $applicant->getDateOfBirth()->format( 'Y-m-d' )
			]
		);
	}

	private function getDataFieldsFromPersonName( PersonName $name ) {
		return [
			'anrede' => $name->getSalutation(),
			'titel' => $name->getTitle(),
			'vorname' => $name->getFirstName(),
			'nachname' => $name->getLastName(),
			'firma' => $name->getCompanyName(),
			'account_holder' => $name->getFirstName() . ' ' . $name->getLastName()
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
	 * @return MembershipApplication|null
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ) {
		try {
			$application = $this->getDoctrineApplicationById( $id );
		}
		catch ( ORMException $ex ) {
			throw new GetMembershipApplicationException( $ex );
		}

		if ( $application === null ) {
			return null;
		}

		return $this->newApplicationDomainEntity( $application );
	}

	/**
	 * @param int $id
	 * @return DoctrineApplication|null
	 * @throws ORMException
	 */
	public function getDoctrineApplicationById( int $id ) {
		return $this->entityManager->find( DoctrineApplication::class, $id );
	}

	private function newApplicationDomainEntity( DoctrineApplication $application ): MembershipApplication {
		return new MembershipApplication(
			$application->getId(),
			$application->getMembershipType(),
			new MembershipApplicant(
				$this->newPersonName( $application ),
				$this->newAddress( $application ),
				new EmailAddress( $application->getApplicantEmailAddress() ),
				new PhoneNumber( $application->getApplicantPhoneNumber() ),
				$application->getApplicantDateOfBirth()
			),
			new MembershipPayment(
				$application->getPaymentIntervalInMonths(),
				Euro::newFromFloat( $application->getPaymentAmount() ),
				$this->newBankData( $application )
			),
			MembershipApplication::NO_MODERATION_NEEDED, // TODO
			MembershipApplication::IS_CURRENT // TODO
		);
	}

	private function newPersonName( DoctrineApplication $application ): PersonName {
		$personName = PersonName::newPrivatePersonName();

		$personName->setFirstName( $application->getApplicantFirstName() );
		$personName->setLastName( $application->getApplicantLastName() );
		$personName->setSalutation( $application->getApplicantSalutation() );
		$personName->setTitle( $application->getApplicantTitle() );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress( DoctrineApplication $application ): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setCity( $application->getCity() );
		$address->setCountryCode( $application->getCountry() );
		$address->setPostalCode( $application->getPostcode() );
		$address->setStreetAddress( $application->getAddress() );

		return $address->freeze()->assertNoNullFields();
	}

	private function newBankData( DoctrineApplication $application ): BankData {
		$bankData = new BankData();

		$bankData->setAccount( $application->getPaymentBankAccount() );
		$bankData->setBankCode( $application->getPaymentBankCode() );
		$bankData->setBankName( $application->getPaymentBankName() );
		$bankData->setBic( $application->getPaymentBic() );
		$bankData->setIban( new Iban( $application->getPaymentIban() ) );

		return $bankData->freeze()->assertNoNullFields();
	}

}
