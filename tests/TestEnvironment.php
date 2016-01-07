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

		return $instance;
	}

	/**
	 * @var FunFunFactory
	 */
	private $factory;

	private function __construct() {
		$this->factory = FunFunFactory::newFromConfig();
	}

	public function getFactory(): FunFunFactory {
		return $this->factory;
	}

}
