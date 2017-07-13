<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Euro\Euro;
use WMDE\Fundraising\Entities\Donation as DoctrineDonation;
use WMDE\Fundraising\Entities\DonationPayments\SofortPayment as DoctrineSofortPayment;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationComment;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationTrackingInfo;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\CreditCardPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\CreditCardTransactionData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentWithoutAssociatedData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PayPalPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Infrastructure\CreditCardExpiry;

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

	public function storeDonation( Donation $donation ): void {
		if ( $donation->getId() === null ) {
			$this->insertDonation( $donation );
		}
		else {
			$this->updateDonation( $donation );
		}
	}

	private function insertDonation( Donation $donation ): void {
		$doctrineDonation = new DoctrineDonation();
		$this->updateDonationEntity( $doctrineDonation, $donation );

		try {
			$this->entityManager->persist( $doctrineDonation );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreDonationException( $ex );
		}

		$donation->assignId( $doctrineDonation->getId() );
	}

	private function updateDonation( Donation $donation ): void {
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

	private function updateDonationEntity( DoctrineDonation $doctrineDonation, Donation $donation ): void {
		$doctrineDonation->setId( $donation->getId() );
		$this->updatePaymentInformation( $doctrineDonation, $donation );
		$this->updateDonorInformation( $doctrineDonation, $donation->getDonor() );
		$this->updateComment( $doctrineDonation, $donation->getComment() );
		$doctrineDonation->setDonorOptsIntoNewsletter( $donation->getOptsIntoNewsletter() );

		$doctrineDonation->encodeAndSetData( array_merge(
			$doctrineDonation->getDecodedData(),
			$this->getDataMap( $donation )
		) );
	}

	private function updatePaymentInformation( DoctrineDonation $doctrineDonation, Donation $donation ): void {
		$doctrineDonation->setStatus( $donation->getStatus() );
		$doctrineDonation->setAmount( $donation->getAmount()->getEuroString() );
		$doctrineDonation->setPaymentIntervalInMonths( $donation->getPaymentIntervalInMonths() );

		$doctrineDonation->setPaymentType( $donation->getPaymentType() );
		$doctrineDonation->setBankTransferCode( self::getBankTransferCode( $donation->getPaymentMethod() ) );

		$paymentMethod = $donation->getPaymentMethod();
		if ( $paymentMethod instanceof SofortPayment ) {
			$doctrineSofortPayment = new DoctrineSofortPayment();
			$doctrineSofortPayment->setConfirmedAt( $paymentMethod->getConfirmedAt() );
			$doctrineDonation->setPayment( $doctrineSofortPayment );
		}
	}

	private function updateDonorInformation( DoctrineDonation $doctrineDonation, Donor $donor = null ): void {
		if ( $donor === null ) {
			if ( $doctrineDonation->getId() === null ) {
				$doctrineDonation->setDonorFullName( 'Anonym' );
			}
		} else {
			$doctrineDonation->setDonorCity( $donor->getPhysicalAddress()->getCity() );
			$doctrineDonation->setDonorEmail( $donor->getEmailAddress() );
			$doctrineDonation->setDonorFullName( $donor->getName()->getFullName() );
		}
	}

	private function updateComment( DoctrineDonation $doctrineDonation, DonationComment $comment = null ): void {
		if ( $comment === null ) {
			$doctrineDonation->setIsPublic( false );
			$doctrineDonation->setComment( '' );
			$doctrineDonation->setPublicRecord( '' );
		} else {
			$doctrineDonation->setIsPublic( $comment->isPublic() );
			$doctrineDonation->setComment( $comment->getCommentText() );
			$doctrineDonation->setPublicRecord( $comment->getAuthorDisplayName() );
		}
	}

	public static function getBankTransferCode( PaymentMethod $paymentMethod ): string {
		if ( $paymentMethod instanceof BankTransferPayment ) {
			return $paymentMethod->getBankTransferCode();
		} elseif ( $paymentMethod instanceof SofortPayment ) {
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

	private function getDataFieldsFromTrackingInfo( DonationTrackingInfo $trackingInfo ): array {
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

		if ( $paymentMethod instanceof CreditCardPayment ) {
			return $this->getDataFieldsFromCreditCardData( $paymentMethod->getCreditCardData() );
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
			return [ 'adresstyp' => 'anonym' ];
		}

		return array_merge(
			$this->getDataFieldsFromPersonName( $personalInfo->getName() ),
			$this->getDataFieldsFromAddress( $personalInfo->getPhysicalAddress() ),
			[ 'email' => $personalInfo->getEmailAddress() ]
		);
	}

	private function getDataFieldsFromPersonName( DonorName $name ): array {
		return [
			'adresstyp' => $name->getPersonType(),
			'anrede' => $name->getSalutation(),
			'titel' => $name->getTitle(),
			'vorname' => $name->getFirstName(),
			'nachname' => $name->getLastName(),
			'firma' => $name->getCompanyName(),
		];
	}

	private function getDataFieldsFromAddress( DonorAddress $address ): array {
		return [
			'strasse' => $address->getStreetAddress(),
			'plz' => $address->getPostalCode(),
			'ort' => $address->getCity(),
			'country' => $address->getCountryCode(),
		];
	}

	private function getDataFieldsFromPayPalData( PayPalData $payPalData ): array {
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

	private function getDataFieldsFromCreditCardData( CreditCardTransactionData $ccData ): array {
		return [
			'ext_payment_id' => $ccData->getTransactionId(),
			'ext_payment_status' => $ccData->getTransactionStatus(),
			'ext_payment_timestamp' => $ccData->getTransactionTimestamp()->format( \DateTime::ATOM ),
			'mcp_amount' => $ccData->getAmount()->getEuroString(),
			'ext_payment_account' => $ccData->getCustomerId(),
			'mcp_sessionid' => $ccData->getSessionId(),
			'mcp_auth' => $ccData->getAuthId(),
			'mcp_title' => $ccData->getTitle(),
			'mcp_country' => $ccData->getCountryCode(),
			'mcp_currency' => $ccData->getCurrencyCode(),
			'mcp_cc_expiry_date' => $this->getExpirationDateAsString( $ccData->getCardExpiry() )
		];
	}

	private function getExpirationDateAsString( CreditCardExpiry $cardExpiry = null ): string {
		if ( $cardExpiry === null ) {
			return '';
		}

		return implode( '/', [ $cardExpiry->getMonth(), $cardExpiry->getYear() ] );
	}

	public function getDonationById( int $id ): ?Donation {
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
	public function getDoctrineDonationById( int $id ): ?DoctrineDonation {
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
			$this->getTrackingInfoFromEntity( $dd ),
			$this->getCommentFromEntity( $dd )
		);
	}

	private function getDonorFromEntity( DoctrineDonation $dd ): ?Donor {
		if ( !$this->entityHasDonorInformation( $dd ) ) {
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
		switch ( $dd->getPaymentType() ) {
			case PaymentType::BANK_TRANSFER:
				return new BankTransferPayment( $dd->getBankTransferCode() );
			case PaymentType::DIRECT_DEBIT:
				return new DirectDebitPayment( $this->getBankDataFromEntity( $dd ) );
			case PaymentType::PAYPAL:
				return new PayPalPayment( $this->getPayPalDataFromEntity( $dd ) );
			case PaymentType::CREDIT_CARD:
				return new CreditCardPayment( $this->getCreditCardDataFromEntity( $dd ) );
			case PaymentType::SOFORT:
				$sofortPayment = new SofortPayment( $dd->getBankTransferCode() );
				$doctrinePayment = $dd->getPayment();
				if ( $doctrinePayment instanceof DoctrineSofortPayment ) {
					$sofortPayment->setConfirmedAt( $doctrinePayment->getConfirmedAt() );
				}
				return $sofortPayment;
		}
		return new PaymentWithoutAssociatedData( $dd->getPaymentType() );
	}

	private function getPersonNameFromEntity( DoctrineDonation $dd ): DonorName {
		$data = $dd->getDecodedData();

		$name = $data['adresstyp'] === DonorName::PERSON_COMPANY
			? DonorName::newCompanyName() : DonorName::newPrivatePersonName();

		$name->setSalutation( $data['anrede'] );
		$name->setTitle( $data['titel'] );
		$name->setFirstName( $data['vorname'] );
		$name->setLastName( $data['nachname'] );
		$name->setCompanyName( $data['firma'] );

		return $name->freeze()->assertNoNullFields();
	}

	private function getPhysicalAddressFromEntity( DoctrineDonation $dd ): DonorAddress {
		$data = $dd->getDecodedData();

		$address = new DonorAddress();

		$address->setStreetAddress( $data['strasse'] );
		$address->setCity( $data['ort'] );
		$address->setPostalCode( $data['plz'] );
		$address->setCountryCode( $data['country'] );

		return $address->freeze()->assertNoNullFields();
	}

	private function getBankDataFromEntity( DoctrineDonation $dd ): BankData {
		$data = $dd->getDecodedData();

		$bankData = new BankData();
		$bankData->setIban( new Iban( $data['iban'] ?? '' ) );
		$bankData->setBic( $data['bic'] ?? '' );
		$bankData->setAccount( $data['konto'] ?? '' );
		$bankData->setBankCode( $data['blz'] ?? '' );
		$bankData->setBankName( $data['bankname'] ?? '' );
		return $bankData->freeze()->assertNoNullFields();

	}

	private function getTrackingInfoFromEntity( DoctrineDonation $dd ): DonationTrackingInfo {
		$data = $dd->getDecodedData();

		$trackingInfo = new DonationTrackingInfo();

		$trackingInfo->setLayout( $data['layout'] ?? '' );
		$trackingInfo->setTotalImpressionCount( intval( $data['impCount'] ?? '0', 10 ) );
		$trackingInfo->setSingleBannerImpressionCount( intval( $data['bImpCount'] ?? '0', 10 ) );
		$trackingInfo->setTracking( $data['tracking'] ?? '' );
		$trackingInfo->setSkin( $data['skin'] ?? '' );
		$trackingInfo->setColor( $data['color'] ?? '' );
		$trackingInfo->setSource( $data['source'] ?? '' );

		return $trackingInfo->freeze()->assertNoNullFields();
	}

	private function getPayPalDataFromEntity( DoctrineDonation $dd ): ?PayPalData {
		$data = $dd->getDecodedData();

		if ( array_key_exists( 'paypal_payer_id', $data ) ) {
			return ( new PayPalData() )
				->setPayerId( $data['paypal_payer_id'] )
				->setSubscriberId( $data['paypal_subscr_id'] ?? '' )
				->setPayerStatus( $data['paypal_payer_status'] ?? '' )
				->setAddressStatus( $data['paypal_address_status'] ?? '' )
				->setAmount( Euro::newFromString( $data['paypal_mc_gross'] ?? '0' ) )
				->setCurrencyCode( $data['paypal_mc_currency'] ?? '' )
				->setFee( Euro::newFromString( $data['paypal_mc_fee'] ?? '0' ) )
				->setSettleAmount( Euro::newFromString( $data['paypal_settle_amount'] ?? '0' ) )
				->setFirstName( $data['paypal_first_name'] ?? '' )
				->setLastName( $data['paypal_last_name'] ?? '' )
				->setAddressName( $data['paypal_address_name'] ?? '' )
				->setPaymentId( $data['ext_payment_id'] ?? '' )
				->setPaymentType( $data['ext_payment_type'] ?? '' )
				->setPaymentStatus( $data['ext_payment_status'] ?? '' )
				->setPaymentTimestamp( $data['ext_payment_timestamp'] ?? '' )
				->freeze()->assertNoNullFields();
		}

		return null;
	}

	private function getCreditCardDataFromEntity( DoctrineDonation $dd ): CreditCardTransactionData {
		$data = $dd->getDecodedData();

		return ( new CreditCardTransactionData() )
			->setTransactionId( $data['ext_payment_id'] ?? '' )
			->setTransactionStatus( $data['ext_payment_status'] ?? '' )
			->setTransactionTimestamp( new \DateTime( $data['ext_payment_timestamp'] ?? 'now' ) )
			->setAmount( Euro::newFromString( $data['mcp_amount'] ?? '0' ) )
			->setCustomerId( $data['ext_payment_account'] ?? '' )
			->setSessionId( $data['mcp_sessionid'] ?? '' )
			->setAuthId( $data['mcp_auth'] ?? '' )
			->setCardExpiry( CreditCardExpiry::newFromString( $data['mcp_cc_expiry_date'] ?? '' ) )
			->setTitle( $data['mcp_title'] ?? '' )
			->setCountryCode( $data['mcp_country'] ?? '' )
			->setCurrencyCode( $data['mcp_currency'] ?? '' )
			->freeze();
	}

	private function getCommentFromEntity( DoctrineDonation $dd ): ?DonationComment {
		if ( $dd->getComment() === '' ) {
			return null;
		}

		return new DonationComment(
			$dd->getComment(),
			$dd->getIsPublic(),
			$dd->getPublicRecord()
		);
	}

	private function entityHasDonorInformation( DoctrineDonation $dd ): bool {
		// If entity was backed up, its information was purged
		if ( $dd->getDtBackup() !== null ) {
			return false;
		}

		$data = $dd->getDecodedData();
		return isset( $data['adresstyp'] ) && $data['adresstyp'] !== DonorName::PERSON_ANONYMOUS;
	}

}
