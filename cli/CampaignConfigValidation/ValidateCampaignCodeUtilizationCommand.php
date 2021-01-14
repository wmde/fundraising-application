<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\CampaignConfigValidation;

use FileFetcher\SimpleFileFetcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\Frontend\BucketTesting\CampaignCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignUtilizationValidator;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\FeatureToggleParser;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

/**
 * @license GPL-2.0-or-later
 */
class ValidateCampaignCodeUtilizationCommand extends Command {

	private const NAME = 'app:validate:campaigns:utilization';
	private const RETURN_CODE_OK = 0;
	private const RETURN_CODE_ERROR = 1;

	/** @see \WMDE\Fundraising\Frontend\Factories\ChoiceFactory */
	private const CHOICE_FACTORY_LOCATION = 'src/Factories/ChoiceFactory.php';

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
		$errorLogger = new CampaignErrorCollection();
		$validator = new CampaignUtilizationValidator(
			$this->getCampaigns( $input->getArgument( 'environment' ) ),
			[ 'campaigns.skins.test' ],
			FeatureToggleParser::getFeatureToggleChecks( self::CHOICE_FACTORY_LOCATION ),
			$errorLogger
		);

		if ( $validator->isPassing() ) {
			return self::RETURN_CODE_OK;
		}

		foreach ( $errorLogger->getErrors() as $error ) {
			$output->writeln( $error );
		}
		$output->writeln( 'Campaign utilization validation failed.' );
		return self::RETURN_CODE_ERROR;
	}

	private function getCampaigns( string $environment ): CampaignCollection {
		$bootstrapper = new EnvironmentBootstrapper( $environment );
		$factory = $bootstrapper->newFunFunFactory();
		return $factory->getCampaignCollection();
	}

}
