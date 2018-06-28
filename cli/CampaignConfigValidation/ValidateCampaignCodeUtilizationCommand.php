<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\CampaignConfigValidation;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignUtilizationValidator;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\FeatureToggleParser;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * @license GNU GPL v2+
 */
class ValidateCampaignCodeUtilizationCommand extends Command {

	const NAME = 'app:validate:campaigns:utilization';
	const ERROR_RETURN_CODE = 1;
	const OK_RETURN_CODE = 0;

	/** @see \WMDE\Fundraising\Frontend\Factories\ChoiceFactory */
	const CHOICE_FACTORY_LOCATION = 'src/Factories/ChoiceFactory.php';

	protected function configure(): void {
		$this->setName( self::NAME )
			->setDescription( 'Validate campaign configuration utilization in source code' )
			->setHelp(
				'This command validates that all campaign configurations are related to a specific entry point in the code'
			)
			->setDefinition(
				new InputDefinition(
					[
						new InputArgument(
							'environment',
							InputArgument::OPTIONAL,
							'Specify a specific campaign environment which is to be tested',
							'prod'
						)
					]
				)
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$errorLogger = new ValidationErrorLogger();
		$validator = new CampaignUtilizationValidator(
			$this->getCampaigns( $input->getArgument( 'environment' ) ),
			[ 'campaigns.skins.test' ],
			FeatureToggleParser::getFeatureToggleChecks( self::CHOICE_FACTORY_LOCATION ),
			$errorLogger
		);

		if ( $validator->isPassing() ) {
			return self::OK_RETURN_CODE;
		}

		foreach ( $errorLogger->getErrors() as $error ) {
			$output->writeln( $error );
		}
		$output->writeln( 'Campaign utilization validation failed.' );
		return self::ERROR_RETURN_CODE;
	}

	private function getFactory( string $environment ): FunFunFactory {
		$environmentConfigPath = __DIR__ . '/../../app/config/config.' . $environment . '.json';
		if ( is_readable( $environmentConfigPath ) === false ) {
			throw new FileNotFoundException( null, 0, null, $environmentConfigPath );
		}
		$configReader = new ConfigReader(
			new \FileFetcher\SimpleFileFetcher(),
			__DIR__ . '/../../app/config/config.dist.json',
			$environmentConfigPath
		);

		return new FunFunFactory( $configReader->getConfig() );
	}

	private function getCampaigns( string $environment ): CampaignCollection {
		$factory = $this->getFactory( $environment );
		return $factory->getCampaignCollection();
	}

}