<?php

namespace WMDE\Fundraising\Frontend\Tests;

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
		$this->factory = new FunFunFactory( [
			'db' => [
				'driver' => 'pdo_sqlite',
				'memory' => true,
			],
			'cms-wiki-url' => 'http://cms.wiki/'
		] );
	}

	public function getFactory(): FunFunFactory {
		return $this->factory;
	}

}
