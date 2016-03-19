<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Domain\BankDataConverter;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\TrackingInfo;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Infrastructure\AuthorizationUpdateException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizationUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Frontend\Domain\TransferCodeGenerator;
use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;
use WMDE\Fundraising\Frontend\Domain\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\Presentation\GreetingGenerator;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Validation\ConstraintViolation;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationUseCase {

	private $donationRepository;
	private $donationValidator;
	private $referrerGeneralizer;
	private $mailer;
	private $transferCodeGenerator;
	private $bankDataConverter;
	private $tokenGenerator;
	private $authorizationUpdater;

	public function __construct( DonationRepository $donationRepository, DonationValidator $donationValidator,
								 ReferrerGeneralizer $referrerGeneralizer, TemplateBasedMailer $mailer,
								 TransferCodeGenerator $transferCodeGenerator, BankDataConverter $bankDataConverter,
								 TokenGenerator $tokenGenerator, DonationAuthorizationUpdater $authorizationUpdater ) {

		$this->donationRepository = $donationRepository;
		$this->donationValidator = $donationValidator;
		$this->referrerGeneralizer = $referrerGeneralizer;
		$this->mailer = $mailer;
		$this->transferCodeGenerator = $transferCodeGenerator;
		$this->bankDataConverter = $bankDataConverter;
		$this->tokenGenerator = $tokenGenerator;
		$this->authorizationUpdater = $authorizationUpdater;
	}

	public function addDonation( AddDonationRequest $donationRequest ): AddDonationResponse {
		$donation = $this->newDonationFromRequest( $donationRequest );

		$validationResult = $this->donationValidator->validate( $donation );

		if ( $validationResult->hasViolations() ) {
			return AddDonationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		if ( $donation->getPaymentType() === PaymentType::BANK_TRANSFER ) {
			$donation->setBankTransferCode( $this->transferCodeGenerator->generateTransferCode() );
		}

		$needsModeration = $this->donationValidator->needsModeration( $donation );

		$donation->setStatus( $this->getInitialDonationStatus( $donation, $needsModeration ) );

		$this->donationRepository->storeDonation( $donation );

		try {
			$updateToken = $this->assignAndReturnNewUpdateToken( $donation->getId() );
			$accessToken = $this->assignAndReturnNewAccessToken( $donation->getId() );
		}
		catch ( AuthorizationUpdateException $ex ) {
			// TODO: rollback side effects on failure

			// TODO: the result format format is really weird for this failure case
			return AddDonationResponse::newFailureResponse( [ new ConstraintViolation(
				null,
				'TODO'
			) ] );
		}

		$this->sendDonationConfirmationEmail( $donation, $needsModeration );

		return AddDonationResponse::newSuccessResponse( $donation, $updateToken, $accessToken );
	}

	/**
	 * @throws AuthorizationUpdateException
	 */
	private function assignAndReturnNewUpdateToken( int $donationId ): string {
		$updateToken = $this->tokenGenerator->generateToken();

		$this->authorizationUpdater->allowModificationViaToken(
			$donationId,
			$updateToken,
			$this->tokenGenerator->generateTokenExpiry()
		);

		return $updateToken;
	}

	/**
	 * @throws AuthorizationUpdateException
	 */
	private function assignAndReturnNewAccessToken( int $donationId ): string {
		$accessToken = $this->tokenGenerator->generateToken();

		$this->authorizationUpdater->allowAccessViaToken(
			$donationId,
			$accessToken
		);

		return $accessToken;
	}

	private function getInitialDonationStatus( Donation $donation, bool $needsModeration ): string {
		if ( $needsModeration ) {
			 return Donation::STATUS_MODERATION;
		}

		if ( $donation->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			return Donation::STATUS_NEW;
		}

		if ( $donation->getPaymentType() === PaymentType::BANK_TRANSFER ) {
			return Donation::STATUS_PROMISE;
		}

		return Donation::STATUS_EXTERNAL_INCOMPLETE;
	}

	private function newDonationFromRequest( AddDonationRequest $donationRequest ): Donation {
		$donation = new Donation();

		$donation->setAmount( $donationRequest->getAmount() );
		$donation->setInterval( $donationRequest->getInterval() );
		$donation->setPersonalInfo( $donationRequest->getPersonalInfo() );
		$donation->setOptsIntoNewsletter( $donationRequest->getOptIn() === '1' );
		$donation->setPaymentType( $donationRequest->getPaymentType() );

		$donation->setTrackingInfo( $this->newTrackingInfoFromRequest( $donationRequest ) );

		if ( $donationRequest->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			$donation->setBankData( $this->newBankDataFromRequest( $donationRequest ) );
		}

		return $donation;
	}

	private function newBankDataFromRequest( AddDonationRequest $request ): BankData {
		$bankData = new BankData();

		$bankData->setIban( new Iban( $request->getIban() ) )
			->setBic( $request->getBic() )
			->setAccount( $request->getBankAccount() )
			->setBankCode( $request->getBankCode() )
			->setBankName( $request->getBankName() );

		if ( $bankData->hasIban() && !$bankData->hasCompleteLegacyBankData() ) {
			$bankData = $this->newBankDataFromIban( $bankData->getIban() );
		}

		if ( $bankData->hasCompleteLegacyBankData() && !$bankData->hasIban() ) {
			$bankData = $this->newBankDataFromAccountAndBankCode( $bankData->getAccount(), $bankData->getBankCode() );
		}

		return $bankData->freeze()->assertNoNullFields();
	}

	private function newBankDataFromIban( Iban $iban ): BankData {
		$bankData = $this->bankDataConverter->getBankDataFromIban( $iban );
		return $bankData->freeze()->assertNoNullFields();
	}

	private function newBankDataFromAccountAndBankCode( string $account, string $bankCode ): BankData {
		$bankData = $this->bankDataConverter->getBankDataFromAccountData( $account, $bankCode );
		return $bankData->freeze()->assertNoNullFields();
	}

	private function newTrackingInfoFromRequest( AddDonationRequest $request ): TrackingInfo {
		$trackingInfo = new TrackingInfo();

		$trackingInfo->setTracking( $request->getTracking() );
		$trackingInfo->setSource( $this->referrerGeneralizer->generalize( $request->getSource() ) );
		$trackingInfo->setTotalImpressionCount( $request->getTotalImpressionCount() );
		$trackingInfo->setSingleBannerImpressionCount( $request->getSingleBannerImpressionCount() );
		$trackingInfo->setColor( $request->getColor() );
		$trackingInfo->setSkin( $request->getSkin() );
		$trackingInfo->setLayout( $request->getLayout() );

		return $trackingInfo->freeze()->assertNoNullFields();
	}

	/**
	 * @param Donation $donation
	 * @param bool $needsModeration
	 *
	 * @throws \RuntimeException
	 * TODO: handle exception
	 */
	private function sendDonationConfirmationEmail( Donation $donation, bool $needsModeration ) {
		if ( $donation->getPersonalInfo() !== null ) {
			$this->mailer->sendMail(
				new MailAddress( $donation->getPersonalInfo()->getEmailAddress() ),
				$this->getConfirmationMailTemplateArguments( $donation, $needsModeration )
			);
		}
	}

	private function getConfirmationMailTemplateArguments( Donation $donation, bool $needsModeration ): array {
		return [
			'recipient' => [
				'salutation' => ( new GreetingGenerator() )->createGreeting(
					$donation->getPersonalInfo()->getPersonName()->getLastName(),
					$donation->getPersonalInfo()->getPersonName()->getSalutation(),
					$donation->getPersonalInfo()->getPersonName()->getTitle()
				)
			],
			'donation' => [
				'id' => $donation->getId(),
				'amount' => $donation->getAmount(),
				'interval' => $donation->getInterval(),
				'needsModeration' => $needsModeration,
				'paymentType' => $donation->getPaymentType(),
				'bankTransferCode' => $donation->getBankTransferCode(),
			]
		];
	}

}