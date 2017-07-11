<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TrimValuesTest extends WebRouteTestCase {

	/**
	 * @dataProvider getTestData
	 */
	public function testPassedGetParametersAreTrimmed( array $expected, array $request ): void {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'GET', '/actually-every-route', $request );

		$this->assertSame( $expected, $client->getRequest()->query->all() );
	}

	/**
	 * @dataProvider getTestData
	 */
	public function testPassedPostParametersAreTrimmed( array $expected, array $request ): void {
		$client = $this->createClient( [], null, self::DISABLE_DEBUG );
		$client->request( 'POST', '/actually-every-route', $request );

		$this->assertSame( $expected, $client->getRequest()->request->all() );
	}

	public function getTestData(): array {
		return [
			[
				[
					'var1' => 'val1',
					'var2' => 'val2',
					'var3' => 'val3',
					'var4' => 'val4',
					'var5' => 0,
					'var6' => 1234.56
				],
				[
					'var1' => '  val1 ',
					'var2' => 'val2 ',
					'var3' => ' val3 ',
					'var4' => '   val4  ',
					'var5' => 0,
					'var6' => 1234.56
				]
			]
		];
	}
}
