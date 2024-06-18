<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\CampaignConfigValidation;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignErrorCollection;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignValidator;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\LoggingCampaignConfigurationLoader;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;

class ValidateCampaignConfigCommand extends Command {

	private const NAME = 'app:validate:campaigns';
	private const RETURN_CODE_OK = 0;
	private const RETURN_CODE_ERROR = 1;

	protected function configure(): void {
		$this->setName( self::NAME )
			->setDescription( 'Validate campaign configuration files' )
			->setHelp(
				'This command validates the Campaign configuration files for errors after they have been merged'
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
		/** @var string $environment */
		$environment = $input->getArgument( 'environment' );
		$errorLogger = new CampaignErrorCollection();
		$bootstrapper = new EnvironmentBootstrapper( $environment );
		$factory = $bootstrapper->newFunFunFactory();
		$factory->setCampaignConfigurationLoader(
			new LoggingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(), $errorLogger
			)
		);

		$campaignCollection = $factory->getCampaignCollection();
		if ( $errorLogger->hasErrors() === false ) {
			$validator = new CampaignValidator( $campaignCollection, $errorLogger );

			if ( $validator->isPassing() ) {
				return self::RETURN_CODE_OK;
			}
		}

		foreach ( $errorLogger->getErrors() as $error ) {
			$output->writeln( $error );
		}
		$output->writeln( 'Campaign YAML validation failed.' );
		return self::RETURN_CODE_ERROR;
	}
}
