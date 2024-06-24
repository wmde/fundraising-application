<?php
declare( strict_types=1 );

namespace Integration\Infrastructure\MembershipBannerCounting;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipBannerCounting\DatabaseMembershipImpressionCounter;

#[CoversClass( DatabaseMembershipImpressionCounter::class )]
class DatabaseMembershipImpressionCounterTest extends KernelTestCase {
	#[DoesNotPerformAssertions]
	public function testCanaryCheckForClassRemoval(): void {
		if ( strtotime( '2024-07-01' ) < time() ) {
			$this->fail( 'The Membership Impression Count feature was only for the 2023 thank you campaign and should be removed' );
		}
	}

	public function testItInsertsImpressionsAsIndividualEntries(): void {
		/** @var FunFunFactory $factory */
		$factory = self::getContainer()->get( FunFunFactory::class );
		$db = $factory->getConnection();
		$counter = new DatabaseMembershipImpressionCounter( $db );
		$this->setupTable( $db );

		$counter->countImpressions( 1, 2, 'thankyou-2023/desktop-ctrl' );
		$counter->countImpressions( 1, 2, 'thankyou-2023/desktop-ctrl' );
		$counter->countImpressions( 2, 2, 'thankyou-2023/desktop-ctrl' );
		$counter->countImpressions( 1, 5, 'thankyou-2023/desktop-ctrl' );
		$counter->countImpressions( 1, 1, 'thankyou-2023/desktop-var' );

		$this->assertSame(
			5,
			$db->fetchOne(
				'SELECT COUNT(*) FROM membership_impression_count',
			)
		);
		$this->assertImpressionCount( $db, 1, 2, 'thankyou-2023/desktop-ctrl', 2 );
		$this->assertImpressionCount( $db, 2, 2, 'thankyou-2023/desktop-ctrl', 1 );
		$this->assertImpressionCount( $db, 1, 5, 'thankyou-2023/desktop-ctrl', 1 );
		$this->assertImpressionCount( $db, 1, 1, 'thankyou-2023/desktop-var', 1 );
	}

	private function assertImpressionCount( Connection $db, int $bannerImpressionCount, int $overallImpressionCount, string $tracking, int $expectedCount ): void {
		$this->assertSame(
			$expectedCount,
			$db->fetchOne(
				'SELECT COUNT(*) FROM membership_impression_count WHERE banner_impression_count = ? AND total_impression_count = ? AND tracking = ?',
				[ $bannerImpressionCount, $overallImpressionCount, $tracking ]
			)
		);
	}

	private function setupTable( Connection $db ): void {
		$db->executeStatement(
			'CREATE TABLE IF NOT EXISTS membership_impression_count (
				id INTEGER UNSIGNED AUTO_INCREMENT,
				banner_impression_count INTEGER NOT NULL,
				total_impression_count INTEGER NOT NULL,
				tracking VARCHAR(255) NOT NULL,
				PRIMARY KEY (id)
			)'
		);
	}
}
