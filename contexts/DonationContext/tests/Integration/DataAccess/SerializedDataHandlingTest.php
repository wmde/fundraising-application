<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class SerializedDataHandlingTest extends \PHPUnit\Framework\TestCase {

	/** @var EntityManager */
	private $entityManager;

	/** @var DonationRepository */
	private $repository;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	/** @dataProvider donationDataProvider */
	public function testDataFieldOfDonationIsInteractedWithCorrectly( $paymentType, $data ) {
		$this->repository = new DoctrineDonationRepository( $this->entityManager );
		$this->storeDonation( $paymentType, $data );

		$donation = $this->repository->getDonationById( 1 );
		$this->repository->storeDonation( $donation );

		/** @var Donation $dd */
		$dd = $this->entityManager->find( Donation::class, 1 );
		$this->assertEquals( $data, $dd->getDecodedData() );
	}

	public function donationDataProvider() {
		return [
			[
				'UEB',
				[
					'id' => 1839605,
					'betrag' => '24.00',
					'periode' => 12,
					'anrede' => 'Herr',
					'titel' => '',
					'vorname' => 'Max',
					'nachname' => 'Muster',
					'plz' => '12345',
					'ort' => 'Smalltown',
					'country' => 'DE',
					'strasse' => 'Erlenkamp 12',
					'email' => 'max.muster@mydomain.com',
					'eintrag' => 'anonym',
					'kommentar' => null,
					'info' => 0,
					'adresstyp' => 'person',
					'dob' => null,
					'phone' => null,
					'source' => 'de.wikipedia.org',
					'tracking' => 'dewiki/wikipedia:spenden',
					'confirmationPage' => '10h16 Bestätigung-UEB',
					'utoken' => '03a448b5ceafb19b954d25688a72973b002ba012',
					'uexpiry' => '2016-05-17 15:42:02',
					'layout' => 'form=10h16_Form1',
					'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0',
					'log' => [
						'2016-05-17 11:42:02' => 'ueb-code generated (1 tries in 0.02 secs)',
					],
					'name' => 'Max Muster',

					// these fields did not exist in the original data set
					'skin' => '',
					'color' => '',
					'firma' => '',

					// these values were stored as strings
					'impCount' => 0,
					'bImpCount' => 0,

					'token' => 'cdd4e95145fb74cb87d7dab8f1e88599'
				]
			],
			[
				'BEZ',
				[
					'id' => '1839609',
					'betrag' => '20.00',
					'periode' => 0,
					'anrede' => 'Herr',
					'titel' => '',
					'vorname' => 'Max',
					'nachname' => 'Muster',
					'plz' => '12345',
					'ort' => 'Smalltown',
					'country' => 'DE',
					'strasse' => 'Erlenkamp 12',
					'email' => 'max.muster@mydomain.com',
					'eintrag' => 'anonym',
					'kommentar' => null,
					'info' => 1,
					'adresstyp' => 'person',
					'dob' => null,
					'phone' => null,
					'source' => 'de.wikipedia.org',
					'tracking' => 'dewiki/wikipedia:spenden',
					'confirmationPage' => '10h16 Bestätigung-BEZ',
					'utoken' => 'ec7c3e8ae5413efe76a3e497455bae9841a85c01',
					'uexpiry' => '2038-05-17 15:58:53',
					'layout' => 'form=10h16_Form1',
					'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) ' .
						'Chrome/50.0.2661.102 Safari/537.36',
					'bankname' => 'Testbank',
					'bic' => 'INGDDEFFXXX',
					'iban' => 'DE12500105170648489890',
					'konto' => '0648489890',
					'blz' => '50010517',
					'log' => [
						'2016-05-17 11:59:21' => 'sepa-mandat confirmed',
					],
					'name' => 'Max Muster',
					'token' => 'ec7c3e8ae5413efe76a3e497455bae9841a85c01',
					'firma' => '',
					'skin' => '',
					'color' => '',

					// these values were stored as strings
					'impCount' => 0,
					'bImpCount' => 0,
				],
			],
			[
				'MCP',
				[
					'id' => '1839659',
					'betrag' => '5.00',
					'periode' => 0,
					'anrede' => 'Herr',
					'titel' => '',
					'vorname' => 'Max',
					'nachname' => 'Muster',
					'plz' => '12345',
					'ort' => 'Smalltown',
					'country' => 'DE',
					'strasse' => 'Erlenkamp 12',
					'email' => 'max.muster@mydomain.com',
					'eintrag' => 'anonym',
					'kommentar' => null,
					'info' => 0,
					'adresstyp' => 'person',
					'dob' => null,
					'phone' => null,
					'source' => 'de.wikipedia.org',
					'tracking' => 'dewiki/wikipedia:spenden',
					'confirmationPage' => '10h16 Bestätigung',
					'utoken' => '6daf07db933ebe146e4596261ca8d5f27e8e2170',
					'uexpiry' => '2016-05-17 19:35:14',
					'layout' => 'form=10h16_Form1',
					'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; rv:46.0) Gecko/20100101 Firefox/46.0',
					'ext_payment_id' => 'spenden.wikimedia.de-IDn24btga9au',
					'ext_payment_status' => 'processed',
					'ext_payment_account' => 'bc11c979141b208631145ce6f211ab0cdf19b92e',
					'ext_payment_type' => 'billing',
					'ext_payment_timestamp' => '2016-05-17T15:36:22+02:00',
					'mcp_amount' => '5.00',
					'mcp_currency' => 'EUR',
					'mcp_country' => 'DE',
					'mcp_auth' => '542e6b75f5718429aad79c5f0faaed16',
					'mcp_title' => 'Ihre Spende an Wikipedia',
					'mcp_sessionid' => 'CC6433ad374048307966bb62230246113042e4fd',
					'mcp_cc_expiry_date' => '12/2038',
					'log' => [
						'2016-05-17 15:36:22' => 'mcp_handler: booked'
					],
					'name' => 'Max Muster',

					// these fields did not exist in the original data set
					'skin' => '',
					'color' => '',
					'firma' => '',

					// these values were stored as strings
					'impCount' => 0,
					'bImpCount' => 0,

					'token' => 'cdd4e95145fb74cb87d7dab8f1e88599'
				]
			],
			[
				'PPL',
				[
					'id' => null,
					'betrag' => '10.00',
					'periode' => 0,
					'anrede' => '',
					'titel' => '',
					'vorname' => 'Max',
					'nachname' => 'Muster',
					'plz' => '12345',
					'ort' => 'Smalltown',
					'country' => 'DE',
					'strasse' => 'Erlenkamp 12',
					'email' => 'max.muster@mydomain.com',
					'eintrag' => 'anonym',
					'kommentar' => null,
					'info' => 0,
					'adresstyp' => 'person',
					'dob' => null,
					'phone' => null,
					'source' => 'web',
					'utoken' => '0e54c50d821f72684ee0f40c6cf4597a07971e02',
					'uexpiry' => '2016-05-17 18:10:35',
					'layout' => '',
					'ext_payment_id' => '72171T32A6H345906',
					'ext_subscr_id' => 'I-DYP3HRBE7WUA',
					'ext_payment_status' => 'Completed/subscr_payment',
					'ext_payment_account' => 'QEEMF34KV3ECL',
					'ext_payment_type' => 'instant',
					'ext_payment_timestamp' => '05:10:30 May 17, 2016 PDT',
					'paypal_payer_id' => 'QEEMF34KV3ECL',
					'paypal_subscr_id' => 'I-DYP3HRBE7WUA',
					'paypal_payer_status' => 'verified',
					'paypal_first_name' => 'Max',
					'paypal_last_name' => 'Muster',
					'paypal_mc_gross' => '10.00',
					'paypal_mc_currency' => 'EUR',
					'paypal_mc_fee' => '0.47',
					'user_agent' => 'PayPal IPN ( https://www.paypal.com/ipn )',
					'name' => 'Max Muster',

					// these fields did not exist in the original data set
					'skin' => '',
					'color' => '',
					'tracking' => '',
					'firma' => '',
					'paypal_settle_amount' => '1.23',
					'paypal_address_name' => 'Max Muster',
					'paypal_address_status' => 'unconfirmed',

					// these values were stored as strings
					'impCount' => 0,
					'bImpCount' => 0,

					'token' => 'cdd4e95145fb74cb87d7dab8f1e88599'
				]
			],
			[
				'PPL',
				[
					'id' => '1839597',
					'betrag' => '24.00',
					'periode' => 12,
					'anrede' => 'Herr',
					'country' => 'DE',
					'eintrag' => 'anonym',
					'kommentar' => null,
					'info' => 0,
					'adresstyp' => 'person',
					'source' => 'de.wikipedia.org',
					'tracking' => 'de.wikipedia.org/sidebar',
					'utoken' => '0463f826b5bf17b298f198c327f54f2557af60bf',
					'uexpiry' => '2016-05-16 23:22:06',
					'layout' => 'form=10h16_Form1',
					'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Trident/7.0; rv:11.0) like Gecko',
					'ext_payment_id' => 'V84052263707282L2',
					'ext_subscr_id' => 'I-RU6JNJC1UV0E',
					'ext_payment_status' => 'Completed/subscr_payment',
					'ext_payment_account' => 'AVSBBESGSB8H8',
					'ext_payment_type' => 'instant',
					'ext_payment_timestamp' => '10:23:09 May 16, 2016 PDT',
					'paypal_payer_id' => 'AVSBBESGSB8H8',
					'paypal_subscr_id' => 'I-RU6JNJC1UV0E',
					'paypal_payer_status' => 'verified',
					'paypal_mc_gross' => '24.00',
					'paypal_mc_currency' => 'EUR',
					'paypal_mc_fee' => '0.64',
					'log' => [
						'2016-05-16 19:23:16' => 'paypal_handler: booked'
					],
					'name' => 'Max Muster',
					'strasse' => 'Erlenkamp 12',
					'ort' => 'Smalltown',
					'plz' => '12345',
					'titel' => '',
					'email' => 'max.muster@mydomain.com',
					'vorname' => 'Max',
					'nachname' => 'Muster',
					'paypal_first_name' => 'Max',
					'paypal_last_name' => 'Muster',

					// these fields did not exist in the original data
					'skin' => '',
					'color' => '',
					'firma' => '',
					'paypal_settle_amount' => '1.23',
					'paypal_address_name' => 'Max Muster',
					'paypal_address_status' => 'unconfirmed',

					// these values were stored as strings
					'impCount' => 0,
					'bImpCount' => 0,

					'token' => 'cdd4e95145fb74cb87d7dab8f1e88599'
				]
			]
		];
	}

	private function storeDonation( string $paymentType, array $data ) {
		$dd = new Donation();
		$dd->setDonorEmail( 'max.muster@mydomain.com' );
		$dd->setAmount( '1.00' );
		$dd->setPaymentType( $paymentType );
		$dd->encodeAndSetData( $data );
		$this->entityManager->persist( $dd );
		$this->entityManager->flush();
	}

}
