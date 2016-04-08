<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineApplication;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplicant;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipPayment;
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
			$this->entityManager->persist( $doctrineApplication );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreMembershipApplicationException( $ex );
		}

		$application->setId( $doctrineApplication->getId() );
	}

	private function newDoctrineApplication( MembershipApplication $application ): DoctrineApplication {
		$doctrineApplication = new DoctrineApplication();

		$this->setApplicantFields( $doctrineApplication, $application->getApplicant() );
		$this->setPaymentFields( $doctrineApplication, $application->getPayment() );

		$doctrineApplication->encodeAndSetData( [] ); // TODO

		return $doctrineApplication;
	}

	private function setApplicantFields( DoctrineApplication $application, MembershipApplicant $applicant ) {
		$application->setFirstName( $applicant->getPersonName()->getFirstName() );
		$application->setLastName( $applicant->getPersonName()->getLastName() );
		$application->setSalutation( $applicant->getPersonName()->getSalutation() );
		$application->setTitle( $applicant->getPersonName()->getTitle() );

		$application->setDob( $applicant->getDateOfBirth() );

		$application->setEmail( $applicant->getEmailAddress() );

		$address = $applicant->getPhysicalAddress();

		$application->setCity( $address->getCity() );
		$application->setCountry( $address->getCountryCode() );
		$application->setPostcode( $address->getPostalCode() );
		$application->setAddress( $address->getStreetAddress() );
	}

	private function setPaymentFields( DoctrineApplication $application, MembershipPayment $payment ) {
		$application->setMembershipType( $payment->getType() );
		$application->setMembershipFeeInterval( $payment->getIntervalInMonths() );
		$application->setMembershipFee( (int)$payment->getAmount()->getEuroFloat() );

		$bankData = $payment->getBankData();

		$application->setAccountNumber( $bankData->getAccount() );
		$application->setBankCode( $bankData->getBankCode() );
		$application->setBankName( $bankData->getBankName() );
		$application->setBic( $bankData->getBic() );
		$application->setIban( $bankData->getIban()->toString() );
	}

	/**
	 * @param int $id
	 *
	 * @return MembershipApplication|null
	 * @throws GetMembershipApplicationException
	 */
	public function getApplicationById( int $id ) {
		// TODO
	}

}
