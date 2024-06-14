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

	/**
	 * @var array<string,class-string>
	 */
	private array $environmentMap;

	/**
	 * @param string $environmentName
	 * @param array<string, class-string> $environmentMap
	 */
	public function __construct(
		private readonly string $environmentName,
		array $environmentMap = []
	) {
		$this->environmentMap = array_merge( self::DEFAULT_ENVIRONMENT_SETUP_MAP, $environmentMap );
	}

	public function getEnvironmentSetupInstance(): EnvironmentSetup {
		if ( !isset( $this->environmentMap[$this->environmentName] ) ) {
			throw new EnvironmentSetupException( $this->environmentName );
		}

		/** @var EnvironmentSetup $class */
		$class = $this->environmentMap[$this->environmentName];
		return new $class;
	}

	public function newFunFunFactory(): FunFunFactory {
		$config = $this->getConfiguration();
		$factory = new FunFunFactory( $config );

		$this->getEnvironmentSetupInstance()
			->setEnvironmentDependentInstances( $factory );

		return $factory;
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function getConfiguration(): array {
		$configReader = ( new EnvironmentDependentConfigReaderFactory( $this->environmentName ) )->getConfigReader();
		return $configReader->getConfig();
	}
}
