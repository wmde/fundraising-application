<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use WMDE\Clock\StubClock;
use WMDE\Fundraising\Frontend\Infrastructure\UserDataKeyGenerator;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\UserDataKeyGenerator
 */
class UserDataKeyGeneratorTest extends TestCase {

	private string $masterKey;

	protected function setUp(): void {
		$this->masterKey = 'DiF8rUjXa2/pePFhQqB5ylDWfH/W5rlEXnrvf5/tNnk=';
	}

	public function testDailyKeyGeneratesSameKeyOnEachCall() {
		$generator = new UserDataKeyGenerator( $this->masterKey, new StubClock( new \DateTimeImmutable( '2021-02-21' ) ) );

		$this->assertSame( $generator->getDailyKey(), $generator->getDailyKey() );
	}

	public function testDailyKeyGeneratesSameKeyForEachDay() {
		$generator1 = new UserDataKeyGenerator( $this->masterKey, new StubClock( new \DateTimeImmutable( '2021-02-20 12:21:00' ) ) );
		$generator2 = new UserDataKeyGenerator( $this->masterKey, new StubClock( new \DateTimeImmutable( '2021-02-20 22:11:00' ) ) );

		$this->assertSame( $generator1->getDailyKey(), $generator2->getDailyKey() );
	}

	public function testDailyKeyGeneratesDifferentKeyForEachDay() {
		$generator1 = new UserDataKeyGenerator( $this->masterKey, new StubClock( new \DateTimeImmutable( '2021-02-21' ) ) );
		$generator2 = new UserDataKeyGenerator( $this->masterKey, new StubClock( new \DateTimeImmutable( '2021-02-22' ) ) );

		$this->assertNotSame( $generator1->getDailyKey(), $generator2->getDailyKey() );
	}
}
