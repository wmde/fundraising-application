<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\CampaignConfigValidation;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\CampaignValidator;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\LoggingCampaignConfigurationLoader;
use WMDE\Fundraising\Frontend\BucketTesting\Validation\ValidationErrorLogger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * @license GNU GPL v2+
 */
class ValidateCampaignConfigCommand extends Command {

	const NAME = 'app:validate:campaigns';
	const ERROR_RETURN_CODE = 1;
	const OK_RETURN_CODE = 0;

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
		$environment = $input->getArgument( 'environment' );
		$errorLogger = new ValidationErrorLogger();
		$factory = $this->getFactory( $environment );
		$factory->setCampaignConfigurationLoader(
			new LoggingCampaignConfigurationLoader(
				$factory->getCampaignConfigurationLoader(), $errorLogger
			)
		);

		$campaignCollection = $factory->getCampaignCollection();
		if ( $errorLogger->hasErrors() ) {
			$validator = new CampaignValidator( $campaignCollection, $errorLogger );

			if ( $validator->isPassing() ) {
				return self::OK_RETURN_CODE;
			}
		}

		foreach ( $errorLogger->getErrors() as $error ) {
			$output->writeln( $error );
		}
		$output->writeln( 'Campaign YAML validation failed.' );
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
}