<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplicant;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Model\MembershipPayment;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhoneNumber;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCase {

	private $repository;
	private $authUpdater;
	private $mailer;
	private $tokenGenerator;

	public function __construct( MembershipApplicationRepository $repository,
		MembershipAppAuthUpdater $authUpdater, TemplateBasedMailer $mailer, TokenGenerator $tokenGenerator ) {

		$this->repository = $repository;
		$this->authUpdater = $authUpdater;
		$this->mailer = $mailer;
		$this->tokenGenerator = $tokenGenerator;
	}

	public function applyForMembership( ApplyForMembershipRequest $request ): ApplyForMembershipResponse {
		$application = $this->newApplicationFromRequest( $request );

		// TODO: validation

		// TODO: handle error
		$this->repository->storeApplication( $application );

		$accessToken = $this->tokenGenerator->generateToken();
		$updateToken = $this->tokenGenerator->generateToken();

		$this->authUpdater->allowAccessViaToken( $application->getId(), $accessToken );
		$this->authUpdater->allowModificationViaToken( $application->getId(), $updateToken );

		$this->sendConfirmationEmail( $application );

		return ApplyForMembershipResponse::newSuccessResponse( $accessToken, $updateToken );
	}

	private function newApplicationFromRequest( ApplyForMembershipRequest $request ) {
		return MembershipApplication::newApplication(
			$request->getMembershipType(),
			new MembershipApplicant(
				$this->newPersonName( $request ),
				$this->newAddress( $request ),
				new EmailAddress( $request->getApplicantEmailAddress() ),
				new PhoneNumber( $request->getApplicantPhoneNumber() ),
				new \DateTime( $request->getApplicantDateOfBirth() )
			),
			new MembershipPayment(
				$request->getPaymentIntervalInMonths(),
				$request->getPaymentAmount(),
				$request->getPaymentBankData()
			)
		);
	}

	private function newPersonName( ApplyForMembershipRequest $request ): PersonName {
		$personName = PersonName::newPrivatePersonName();

		$personName->setFirstName( $request->getApplicantFirstName() );
		$personName->setLastName( $request->getApplicantLastName() );
		$personName->setSalutation( $request->getApplicantSalutation() );
		$personName->setTitle( $request->getApplicantTitle() );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress( ApplyForMembershipRequest $request ): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setCity( $request->getApplicantCity() );
		$address->setCountryCode( $request->getApplicantCountryCode() );
		$address->setPostalCode( $request->getApplicantPostalCode() );
		$address->setStreetAddress( $request->getApplicantStreetAddress() );

		return $address->freeze()->assertNoNullFields();
	}

	private function sendConfirmationEmail( MembershipApplication $application ) {
		$this->mailer->sendMail(
			$application->getApplicant()->getEmailAddress(),
			[] // TODO
		);
	}

}
