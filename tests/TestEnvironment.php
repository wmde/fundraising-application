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
	 * @var FunFunFactory
	 */
	private $factory;

	private function __construct() {
		$configReader = new ConfigReader(
			new SimpleFileFetcher(),
			__DIR__ . '/../app/config/config.dist.json',
			__DIR__ . '/../app/config/config.test.json',
			__DIR__ . '/../app/config/config.test.local.json'
		);

		$this->factory = new FunFunFactory( $configReader->getConfig() );
	}

	public function getFactory(): FunFunFactory {
		return $this->factory;
	}

}
