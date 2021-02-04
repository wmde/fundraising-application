<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use WMDE\Fundraising\Frontend\Factories\EnvironmentDependentConfigReaderFactory;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\DevelopmentEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\EnvironmentSetupException;
use WMDE\Fundraising\Frontend\Factories\EnvironmentSetup\ProductionEnvironmentSetup;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class EnvironmentBootstrapper {

	private const DEFAULT_ENVIRONMENT_SETUP_MAP = [
		'dev' => DevelopmentEnvironmentSetup::class,
		// User Acceptance Testing should be as close to production as possible
		'uat' => ProductionEnvironmentSetup::class,
		'prod' => ProductionEnvironmentSetup::class
	];

	private string $environmentName;

	/**
	 * @var array<string,class-string>
	 */
	private array $environmentMap;

	public function __construct( string $environmentName, array $environmentMap = [] ) {
		$this->environmentName = $environmentName;
		$this->environmentMap = array_merge( self::DEFAULT_ENVIRONMENT_SETUP_MAP, $environmentMap );
	}

	public function getEnvironmentSetupInstance(): EnvironmentSetup {
		if ( !isset( $this->environmentMap[$this->environmentName] ) ) {
			throw new EnvironmentSetupException( $this->environmentName );
		}
		$class = $this->environmentMap[$this->environmentName];
		return new $class;
	}

	public function newFunFunFactory(): FunFunFactory {
		$config = $this->getConfiguration();
		$factory = new FunFunFactory( $config );

		$this->getEnvironmentSetupInstance()
			->setEnvironmentDependentInstances( $factory, $config );

		return $factory;
	}

	protected function getConfiguration(): array {
		$configReader = ( new EnvironmentDependentConfigReaderFactory( $this->environmentName ) )->getConfigReader();
		return $configReader->getConfig();
	}
}
