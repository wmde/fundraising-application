<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TrimValuesTest extends WebRouteTestCase {

	public function testPassedGetParametersAreTrimmed(): void {
		$params = $this->newParams();

		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/actually-every-route', $params );

		$this->assertSame( $this->trimArray( $params ), $client->getRequest()->query->all() );
	}

	public function testPassedPostParametersAreTrimmed(): void {
		$params = $this->newParams();

		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'POST', '/actually-every-route', $params );

		$this->assertSame( $this->trimArray( $params ), $client->getRequest()->request->all() );
	}

	private function newParams(): array {
		return [
			'var1' => '  val1 ',
			'var2' => 'val2 ',
			'var3' => ' val3 ',
			'var4' => '   val4  ',
			'var5' => 0,
			'var6' => 1234.56,
		];
	}

	private function trimArray( array $params ): array {
		array_walk( $params, [ self::class, 'trimValue' ] );
		return $params;
	}

	private function trimValue( &$value ): void {	// @codingStandardsIgnoreLine
		$value = is_string( $value ) ? trim( $value ) : $value;
	}

}
