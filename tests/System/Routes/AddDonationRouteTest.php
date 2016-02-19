<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class AddDonationRouteTest extends WebRouteTestCase {

	private $validFormInput = [
		'betrag' => '5,51',
		'zahlweise' => 'BEZ',
		'periode' => 0,
		'iban' => 'DE12500105170648489890',
		'bic' => 'INGDDEFFXXX',
		'konto' => '0648489890',
		'blz' => '50010517',
		'bankname' => 'ING-DiBa',
		'adresstyp' => 'person',
		'anrede' => 'Frau',
		'titel' => 'Prof. Dr.',
		'firma' => '',
		'vorname' => 'Karla',
		'nachname' => 'Kennichnich',
		'strasse' => 'Lehmgasse 12',
		'plz' => '12345',
		'ort' => 'Einort',
		'country' => 'DE',
		'email' => 'karla@kennichnich.de',
		'info' => '1',
		'piwik_campaign' => 'test',
		'piwik_kwd' => 'gelb',
		'impCount' => '3',
		'bImpCount' => '1',
		'layout' => 'Default',
		'color' => 'blue',
		'skin' => 'default',
	];

	public function testGivenValidRequest_donationGetsPersisted() {
		/**
		 * @var FunFunFactory
		 */
		$factory = null;

		// TODO: refactor once https://github.com/wmde/FundraisingFrontend/commit/7df7c763fb1c6 is merged
		$client = $this->createClient( [], function( FunFunFactory $f ) use ( &$factory ) {
			$factory = $f;
		} );

		$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
		$client->followRedirects( false );

		$client->request(
			'POST',
			'/donation/add',
			$this->validFormInput
		);

		$donation = $this->getDonationFromDatabase( $factory );

		$data = unserialize( base64_decode( $donation->getData() ) );
		$this->assertSame( 5.51, $donation->getAmount() );
		$this->assertSame( 'BEZ', $donation->getPaymentType() );
		$this->assertSame( 0, $donation->getPeriod() );
		$this->assertSame( 'DE12500105170648489890', $data['iban'] );
		$this->assertSame( 'INGDDEFFXXX', $data['bic'] );
		$this->assertSame( '0648489890', $data['konto'] );
		$this->assertSame( '50010517', $data['blz'] );
		$this->assertSame( 'ING-DiBa', $data['bankname'] );
		$this->assertSame( 'person', $data['adresstyp'] );
		$this->assertSame( 'Frau', $data['anrede'] );
		$this->assertSame( 'Prof. Dr.', $data['titel'] );
		$this->assertSame( '', $data['firma'] );
		$this->assertSame( 'Karla', $data['vorname'] );
		$this->assertSame( 'Kennichnich', $data['nachname'] );
		$this->assertSame( 'Prof. Dr. Karla Kennichnich', $donation->getName() );
		$this->assertSame( 'Lehmgasse 12', $data['strasse'] );
		$this->assertSame( '12345', $data['plz'] );
		$this->assertSame( 'Einort', $data['ort'] );
		$this->assertSame( 'Einort', $donation->getCity() );
		$this->assertSame( 'DE', $data['country'] );
		$this->assertSame( 'karla@kennichnich.de', $data['email'] );
		$this->assertSame( 'karla@kennichnich.de', $donation->getEmail() );
		$this->assertSame( 'test/gelb', $data['tracking'] );
		$this->assertSame( 3, $data['impCount'] );
		$this->assertSame( 1, $data['bImpCount'] );
		$this->assertSame( 'Default', $data['layout'] );
		$this->assertSame( 'blue', $data['color'] );
		$this->assertSame( 'default', $data['skin'] );
		$this->assertSame( 'en.wikipedia.org', $data['source'] );
		$this->assertSame( 'N', $donation->getStatus() );
		$this->assertSame( '1', $donation->getInfo() );
		// TODO: another test for bank transfer
		#$this->assertSame( '', $donation->getTransferCode() );
	}

	private function getDonationFromDatabase( FunFunFactory $factory ): Donation {
		$donationRepo = $factory->getEntityManager()->getRepository( Donation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( Donation::class, $donation );
		return $donation;
	}

}
