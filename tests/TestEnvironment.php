<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use FileFetcher\SimpleFileFetcher;
use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Factories\TranslationFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TestEnvironment {

	public static function newInstance( array $config = [] ) {
		$instance = new self( $config );

		$installer = $instance->factory->newInstaller();

		try {
			$installer->uninstall();
		}
		catch ( \Exception $ex ) {
		}

		$installer->install();

		$instance->factory->setNullMessenger();
		$instance->factory->setTwigEnvironment( new \Twig_Environment() );

		return $instance;
	}

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var FunFunFactory
	 */
	private $factory;

	private function __construct( array $config ) {
		$this->config = array_replace_recursive( $this->getConfigFromFiles(), $config );
		$this->factory = new FunFunFactory( $this->config );
	}

	private function getConfigFromFiles() {
		$readerArguments = [
			new SimpleFileFetcher(),
			__DIR__ . '/../app/config/config.dist.json',
			__DIR__ . '/../app/config/config.test.json',
		];

		if ( is_readable( __DIR__ . '/../app/config/config.test.local.json' ) ) {
			$readerArguments[] = __DIR__ . '/../app/config/config.test.local.json';
		}

		/** @noinspection PhpParamsInspection */
		$configReader = new ConfigReader( ...$readerArguments );

		return $configReader->getConfig();
	}

	public function getFactory(): FunFunFactory {
		return $this->factory;
	}

	public function getConfig(): array {
		return $this->config;
	}

	public static function getTestData( string $fileName ): string {
		return file_get_contents( __DIR__ . '/Data/files/' . $fileName );
	}

	public static function getJsonTestData( string $fileName ) {
		return json_decode( self::getTestData( $fileName ), true );
	}

}
