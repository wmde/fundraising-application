<?php

namespace WMDE\Fundraising\Frontend\Tests\System;

use Silex\Application;
use Silex\WebTestCase;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class SystemTestCase extends WebTestCase {

	/**
	 * @var TestEnvironment
	 */
	protected $testEnvironment;

	public function setUp() {
		$this->testEnvironment = TestEnvironment::newInstance();
		parent::setUp();
	}

	public function createApplication() : Application {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ffFactory = $this->testEnvironment->getFactory();
		$app = require __DIR__ . ' /../../app/bootstrap.php';

		$app['debug'] = true;
		unset( $app['exception_handler'] );

		return $app;
	}

}