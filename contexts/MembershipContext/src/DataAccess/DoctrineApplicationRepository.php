<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Entities\MembershipApplication as DoctrineApplication;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Applicant;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\ApplicantAddress;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\ApplicantName;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Payment;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\PhoneNumber;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\ApplicationRepository;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\GetMembershipApplicationException;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Repositories\StoreMembershipApplicationException;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Store\MembershipApplicationData;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineApplicationRepository implements ApplicationRepository {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function storeApplication( Application $application ) {
		if ( $application->hasId() ) {
			$this->updateApplication( $application );
		}
		else {
			$this->insertApplication( $application );
		}
	}

	private function insertApplication( Application $application ) {
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

	private function updateApplication( Application $application ) {
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

	private function updateDoctrineApplication( DoctrineApplication $doctrineApplication, Application $application ) {
		$doctrineApplication->setId( $application->getId() );
		$doctrineApplication->setMembershipType( $application->getType() );

		$this->setApplicantFields( $doctrineApplication, $application->getApplicant() );
		$this->setPaymentFields( $doctrineApplication, $application->getPayment() );

		$doctrineStatus = $this->getDoctrineStatus( $application );
		$this->preserveDoctrineStatus( $doctrineApplication, $doctrineStatus );
		$doctrineApplication->setStatus( $doctrineStatus );
	}

	private function setApplicantFields( DoctrineApplication $application, Applicant $applicant ) {
		$application->setApplicantFirstName( $applicant->getName()->getFirstName() );
		$application->setApplicantLastName( $applicant->getName()->getLastName() );
		$application->setApplicantSalutation( $applicant->getName()->getSalutation() );
		$application->setApplicantTitle( $applicant->getName()->getTitle() );
		$application->setCompany( $applicant->getName()->getCompanyName() );

		$application->setApplicantDateOfBirth( $applicant->getDateOfBirth() );

		$application->setApplicantEmailAddress( $applicant->getEmailAddress()->getFullAddress() );
		$application->setApplicantPhoneNumber( $applicant->getPhoneNumber()->__toString() );

		$address = $applicant->getPhysicalAddress();

		$application->setCity( $address->getCity() );
		$application->setCountry( $address->getCountryCode() );
		$application->setPostcode( $address->getPostalCode() );
		$application->setAddress( $address->getStreetAddress() );
	}

	private function setPaymentFields( DoctrineApplication $application, Payment $payment ) {
		$application->setPaymentIntervalInMonths( $payment->getIntervalInMonths() );
		$application->setPaymentAmount( (int)$payment->getAmount()->getEuroFloat() );
		$paymentMethod = $payment->getPaymentMethod();

		$application->setPaymentType( $paymentMethod->getType() );
		if ( $paymentMethod instanceof DirectDebitPayment ) {
			$this->setBankDataFields( $application, $paymentMethod->getBankData() );
		}
	}

	private function setBankDataFields( DoctrineApplication $application, BankData $bankData ) {
		$application->setPaymentBankAccount( $bankData->getAccount() );
		$application->setPaymentBankCode( $bankData->getBankCode() );
		$application->setPaymentBankName( $bankData->getBankName() );
		$application->setPaymentBic( $bankData->getBic() );
		$application->setPaymentIban( $bankData->getIban()->toString() );
	}

	private function getDoctrineStatus( Application $application ): int {
		$status = DoctrineApplication::STATUS_NEUTRAL;

		if ( $application->needsModeration() ) {
			$status += DoctrineApplication::STATUS_MODERATION;
		}

		if ( $application->isCancelled() ) {
			$status += DoctrineApplication::STATUS_CANCELED;
		}

		if ( $status === DoctrineApplication::STATUS_NEUTRAL ) {
			$status += DoctrineApplication::STATUS_CONFIRMED;
		}

		return $status;
	}

	private function preserveDoctrineStatus( DoctrineApplication $doctrineApplication, int $doctrineStatus ) {
		if ( $doctrineStatus < DoctrineApplication::STATUS_CONFIRMED ) {
			$doctrineApplication->modifyDataObject( function ( MembershipApplicationData $data ) {
				$data->setPreservedStatus( DoctrineApplication::STATUS_CONFIRMED );
			} );
		}
	}

	/**
	 * @param int $id
	 *
	 * @return Application|null
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

	private function newApplicationDomainEntity( DoctrineApplication $application ): Application {
		return new Application(
			$application->getId(),
			$application->getMembershipType(),
			new Applicant(
				$this->newPersonName( $application ),
				$this->newAddress( $application ),
				new EmailAddress( $application->getApplicantEmailAddress() ),
				new PhoneNumber( $application->getApplicantPhoneNumber() ),
				$application->getApplicantDateOfBirth()
			),
			new Payment(
				$application->getPaymentIntervalInMonths(),
				Euro::newFromFloat( $application->getPaymentAmount() ),
				$this->newPaymentMethod( $application )
			),
			$application->needsModeration(),
			$application->isCancelled()
		);
	}

	private function newPersonName( DoctrineApplication $application ): ApplicantName {
		$personName = ApplicantName::newPrivatePersonName();

		$personName->setFirstName( $application->getApplicantFirstName() );
		$personName->setLastName( $application->getApplicantLastName() );
		$personName->setSalutation( $application->getApplicantSalutation() );
		$personName->setTitle( $application->getApplicantTitle() );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress( DoctrineApplication $application ): ApplicantAddress {
		$address = new ApplicantAddress();

		$address->setCity( $application->getCity() );
		$address->setCountryCode( $application->getCountry() );
		$address->setPostalCode( $application->getPostcode() );
		$address->setStreetAddress( $application->getAddress() );

		return $address->freeze()->assertNoNullFields();
	}

	private function newPaymentMethod( DoctrineApplication $application ): PaymentMethod {
		if ( $application->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			return new DirectDebitPayment( $this->newBankData( $application ) );
		}

		if ( $application->getPaymentType() === PaymentType::PAYPAL ) {
			return new PayPalPayment();
		}
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
