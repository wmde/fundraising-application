<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * WebTestCase is the base class for integration tests.
 *
 * This class was adapted from Silex\WebTestCase, which still uses PHPUnit 5.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
abstract class WebTestCase extends TestCase {
	/**
	 * HttpKernelInterface instance.
	 *
	 * @var HttpKernelInterface
	 */
	protected $app;

	/**
	 * @var FunFunFactory
	 */
	protected $ffFactory;

	/**
	 * PHPUnit setUp for setting up the application.
	 *
	 * Note: Child classes that define a setUp method must call
	 * parent::setUp().
	 */
	protected function setUp()
	{
		$this->app = $this->createApplication();
	}

	/**
	 * Creates the application.
	 *
	 * @return HttpKernelInterface
	 */
	public function createApplication() {
		$this->ffFactory = $ffFactory = TestEnvironment::newInstance()->getFactory();
		$app = require __DIR__ . '/../../app/bootstrap.php';
		$app['debug'] = true;
		unset($app['exception_handler']);

		$ffFactory->setTwigEnvironment( $app['twig'] );

		return $app;
	}

	/**
	 * Creates a Client.
	 *
	 * @param array $server Server parameters
	 *
	 * @return Client A Client instance
	 */
	public function createClient( array $server = [] )
	{
		if ( !class_exists( 'Symfony\Component\BrowserKit\Client' ) ) {
			throw new \LogicException( 'Component "symfony/browser-kit" is required by WebTestCase.' .
				PHP_EOL .
				'Run composer require symfony/browser-kit'
			);
		}

		return new Client( $this->app, $server );
	}
}
