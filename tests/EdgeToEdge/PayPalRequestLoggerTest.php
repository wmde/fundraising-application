<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WMDE\Fundraising\Frontend\App\EventHandlers\PayPalRequestLogger;
use WMDE\PsrLogTestDoubles\LoggerSpy;

/**
 * @covers \WMDE\Fundraising\Frontend\App\EventHandlers\PayPalRequestLogger
 */
class PayPalRequestLoggerTest extends WebRouteTestCase {

	private array $paypalRoutes = [
		'handle_paypal_payment_notification',
		'handle_paypal_membership_fee_payments'
	];

	private const LOG_DIR = 'log';
	private const LOG_FILENAME = 'test.csv';

	private vfsStreamDirectory $filesystem;
	private KernelBrowser $client;
	private string $filePath;
	private LoggerInterface $logger;

	public function setUp(): void {
		$this->filesystem = vfsStream::setup( self::LOG_DIR );
		$this->filePath = vfsStream::url( self::LOG_DIR . '/' . self::LOG_FILENAME );
		$this->logger = new LoggerSpy();

		/** @var KernelBrowser $client */
		$client = $this->createClient();
		$this->client = $client;
		self::$container->set(
			PayPalRequestLogger::class,
			new PayPalRequestLogger( $this->filePath, $this->paypalRoutes, $this->logger )
		);
	}

	public function testWhenPostedPayPalData_createsLogFile(): void {
		$route = self::newUrlForNamedRoute( $this->paypalRoutes[0] );
		$payPalData = $this->validPayPalData();

		$this->client->request( 'post', $route, $payPalData['post_vars'] );

		$this->assertTrue( $this->filesystem->hasChild( self::LOG_FILENAME ) );
		$this->assertSame( $payPalData['stored'], file_get_contents( $this->filePath ) );
	}

	private static function newUrlForNamedRoute( $routeName ): string {
		return self::$container->get( 'router' )->generate(
			$routeName,
			[],
			UrlGeneratorInterface::RELATIVE_PATH
		);
	}

	public function testWhenPostedPayPalDataAndFileExists_addsDataToLogFile(): void {
		$route = self::newUrlForNamedRoute( $this->paypalRoutes[0] );
		$payPalData = $this->validPayPalData();
		$existingData = 'go, to, school, 99' . PHP_EOL;
		$expectedContents = $existingData . $payPalData['stored'];

		file_put_contents( $this->filePath, $existingData );

		$this->client->request( 'post', $route, $payPalData['post_vars'] );

		$this->assertSame( $expectedContents, file_get_contents( $this->filePath ) );
	}

	/**
	 * @dataProvider routesDataProvider
	 */
	public function testRunsOnPayPalNotificationRoutes( string $routeName ): void {
		$route = self::newUrlForNamedRoute( $routeName );
		$payPalData = $this->validPayPalData();

		$this->client->request( 'post', $route, $payPalData['post_vars'] );

		$this->assertTrue( $this->filesystem->hasChild( self::LOG_FILENAME ) );
	}

	public function testDoesNotRunOnNonPayPalRoute(): void {
		$payPalData = $this->validPayPalData();

		$this->client->request( 'post', '/', $payPalData['post_vars'] );

		$this->assertFalse( $this->filesystem->hasChild( self::LOG_FILENAME ) );
	}

	private function validPayPalData(): array {
		return [
			'post_vars' => [
				'txn_type' => 'express_checkout',
				'txn_id' => '61E67681CH3238416',
				'subscr_id' => '8RHHUM3W3PRH7QY6B59',
				'item_number' => 1,
			],
			'stored' => 'express_checkout,61E67681CH3238416,8RHHUM3W3PRH7QY6B59,1' . PHP_EOL
		];
	}

	public function routesDataProvider(): array {
		return [
			[ $this->paypalRoutes[0] ],
			[ $this->paypalRoutes[1] ],
		];
	}

	public function testLogsFileCreationErrors(): void {
		$route = self::newUrlForNamedRoute( $this->paypalRoutes[0] );
		$payPalData = $this->validPayPalData();
		$this->filesystem->chmod( 0444 );

		$this->client->request( 'post', $route, $payPalData['post_vars'] );

		$this->assertCount( 1, $this->logger->getLogCalls() );
		$this->assertFalse( $this->filesystem->hasChild( self::LOG_FILENAME ) );
	}
}
