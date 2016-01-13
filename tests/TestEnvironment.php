<?php

namespace WMDE\Fundraising\Frontend\Tests;

use FileFetcher\SimpleFileFetcher;
use WMDE\Fundraising\Frontend\ConfigReader;
use WMDE\Fundraising\Frontend\FunFunFactory;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TestEnvironment {

	public static function newInstance() {
		$instance = new self();

		$instance->factory->newInstaller()->install();

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

	private function __construct() {
		$this->config = $this->getConfigFromFiles();
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

	public function getTestData( string $fileName ): string {
		return file_get_contents( __DIR__ . '/data/' . $fileName );
	}

	public function getJsonTestData( string $fileName ) {
		return json_decode( $this->getTestData( $fileName ), true );
	}

}
