<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationTokens;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FixedDonationTokenFetcher;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\ShowDonationConfirmation\ShowDonationConfirmationRequest;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\ShowDonationConfirmation\ShowDonationConfirmationUseCase;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\UseCases\ShowDonationConfirmation\ShowDonationConfirmationUseCase
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationUseCaseTest extends \PHPUnit\Framework\TestCase {

	private const CORRECT_DONATION_ID = 1;
	private const ACCESS_TOKEN = 'some token';
	private const UPDATE_TOKEN = 'some other token';

	public function testWhenAuthorizerSaysNoCanHaz_accessIsNotPermitted(): void {
		$useCase = new ShowDonationConfirmationUseCase(
			new FailingDonationAuthorizer(),
			$this->newFixedTokenFetcher(),
			new FakeDonationRepository( ValidDonation::newDirectDebitDonation() )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

	public function testWhenAuthorizerSaysSureThingBro_accessIsPermitted(): void {
		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			$this->newFixedTokenFetcher(),
			new FakeDonationRepository( ValidDonation::newDirectDebitDonation() )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertTrue( $response->accessIsPermitted() );
	}

	public function testWhenDonationDoesNotExist_accessIsNotPermitted(): void {
		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			$this->newFixedTokenFetcher(),
			new FakeDonationRepository()
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertFalse( $response->accessIsPermitted() );
		$this->assertNull( $response->getDonation() );
	}

	public function testWhenDonationExistsAndAccessIsAllowed_donationIsReturned(): void {
		$donation = ValidDonation::newDirectDebitDonation();

		$useCase = new ShowDonationConfirmationUseCase(
			new SucceedingDonationAuthorizer(),
			$this->newFixedTokenFetcher(),
			new FakeDonationRepository( $donation )
		);

		$response = $useCase->showConfirmation( new ShowDonationConfirmationRequest(
			self::CORRECT_DONATION_ID
		) );

		$this->assertEquals( $donation, $response->getDonation() );
	}

	private function newFixedTokenFetcher() {
		return new FixedDonationTokenFetcher( new DonationTokens( self::ACCESS_TOKEN, self::UPDATE_TOKEN ) );
	}
}
