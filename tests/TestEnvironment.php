<?php

namespace WMDE\Fundraising\Frontend\Tests;

use WMDE\Fundraising\Frontend\FFFactory;

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
	 * @var FFFactory
	 */
	private $factory;

	private function __construct() {
		$this->factory = FFFactory::newFromConfig();
	}

	public function getFactory(): FFFactory {
		return $this->factory;
	}

}
