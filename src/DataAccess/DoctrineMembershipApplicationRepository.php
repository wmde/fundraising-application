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
		$doctrineApplication = $this->newDoctrineApplication( $application );

		try {
			if ( $doctrineApplication->getId() === null ) {
				$this->entityManager->persist( $doctrineApplication );
			}
			else {
				// merge would override the timestamp with null value, so we need to get it from the entity
				$oldApplication = $this->entityManager->find( DoctrineApplication::class, $doctrineApplication->getId() );
				// FIXME: this blows up if the entity is not found
				$doctrineApplication->setCreationTime( $oldApplication->getCreationTime() );
				$this->entityManager->merge( $doctrineApplication );
			}

			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreMembershipApplicationException( $ex );
		}

		$application->assignId( $doctrineApplication->getId() );
	}

	private function newDoctrineApplication( MembershipApplication $application ): DoctrineApplication {
		$doctrineApplication = new DoctrineApplication();

		$doctrineApplication->setId( $application->getId() );
		$doctrineApplication->setMembershipType( $application->getType() );

		$this->setApplicantFields( $doctrineApplication, $application->getApplicant() );
		$this->setPaymentFields( $doctrineApplication, $application->getPayment() );

		$doctrineApplication->encodeAndSetData( [] ); // TODO

		return $doctrineApplication;
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

	/**
	 * @param int $id
	 *
	 * @return MembershipApplication|null
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ) {
		try {
			/**
			 * @var DoctrineApplication $application
			 */
			$application = $this->entityManager->find( DoctrineApplication::class, $id );
		}
		catch ( ORMException $ex ) {
			throw new GetMembershipApplicationException( $ex );
		}

		if ( $application === null ) {
			return null;
		}

		return $this->newApplicationDomainEntity( $application );
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
