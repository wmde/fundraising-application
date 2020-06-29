<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Sunset\Routes;

use Symfony\Component\HttpKernel\Client;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Validation\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Tests\EdgeToEdge\WebRouteTestCase;
use WMDE\FunValidators\Validators\EmailValidator;

/**
 * @license GPL-2.0-or-later
 */
class AddDonationLegacyParametersTest extends WebRouteTestCase {

	private const ADD_DONATION_PATH = '/donation/add';

	public function testFeatureIsDeprecated(): void {
		$expirationDate = new \DateTime( '2021-01-31' );
		$now = new \DateTime();

		$this->assertTrue(
			$expirationDate > $now,
			'Legacy parameters are no longer supported. Please replace the "FallbackRequestValueReader" class with ' .
			' scalar default values and delete the class and this test.' );
	}

	public function testGivenLegacyParameters_donationGetsPersisted(): void {
		$this->createEnvironment( [], function ( Client $client, FunFunFactory $factory ): void {
			$factory->setEmailValidator( new EmailValidator( new NullDomainNameValidator() ) );
			$client->setServerParameter( 'HTTP_REFERER', 'https://en.wikipedia.org/wiki/Karla_Kennichnich' );
			$client->followRedirects( false );

			$client->request(
				'POST',
				self::ADD_DONATION_PATH,
				$this->newLegacyInput()
			);

			$donation = $this->getDonationFromDatabase( $factory );
			$this->assertSame( '5.51', $donation->getAmount() );
			$this->assertSame( 'UEB', $donation->getPaymentType() );
			$this->assertSame( 0, $donation->getPaymentIntervalInMonths() );
		} );
	}

	private function newLegacyInput(): array {
		return [
			'betrag' => '5.51',
			'zahlweise' => 'UEB',
			'periode' => 0,
			'addressType' => 'anonym',
		];
	}

	private function getDonationFromDatabase( FunFunFactory $factory ): Donation {
		$donationRepo = $factory->getEntityManager()->getRepository( Donation::class );
		$donation = $donationRepo->find( 1 );
		$this->assertInstanceOf( Donation::class, $donation );
		return $donation;
	}

}
