<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\System\WebRouteTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationRouteTest extends WebRouteTestCase {

	public function testGivenRequestWithUnknownDonationId_resultIsError() {
		$this->createEnvironment( [], function( Client $client, FunFunFactory $factory ) {
			$client->request(
				'POST',
				'donation/add',
				[
					'betrag' => '10',
					'zahlweise' => 'BEZ',
					'periode' => '0',
					'adresstyp' => 'anonym',
				]
			);

			$donation = $this->getDonationFromDatabase( $factory );

			$this->assertSame( '10', $donation->getAmount() );
			$this->assertSame( 'BEZ', $donation->getPaymentType() );
		} );
	}

	private function getDonationFromDatabase( FunFunFactory $factory ): Donation {
		$donationRepo = $factory->getEntityManager()->getRepository( Donation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( Donation::class, $donation );
		return $donation;
	}

}
