<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation;

use FileFetcher\SimpleFileFetcher;
use JsonSchema\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * @license GPL-2.0-or-later
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidateApplicationConfigCommand extends Command {

	private const NAME = 'app:validate:config';
	private const DEFAULT_SCHEMA = __DIR__ . '/../../app/config/schema.json';
	private const RETURN_CODE_OK = 0;
	private const RETURN_CODE_ERROR = 1;

	protected function configure(): void {
		$this->setName( self::NAME )
			->setDescription( 'Validate configuration files' )
			->setHelp( 'This command merges the specified configuration files and validates them against a JSON schema' )
			->setDefinition(
				new InputDefinition( [
					new InputOption(
						'schema',
						's',
						InputOption::VALUE_REQUIRED,
						'JSON schema file',
						realpath( self::DEFAULT_SCHEMA )
					),
					new InputOption(
						'dump-config',
						'd',
						InputOption::VALUE_NONE,
						'Dump merged configuration'
					),
					new InputArgument( 'config_file', InputArgument::REQUIRED + InputArgument::IS_ARRAY ),
				] )
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$configObject = $this->loadConfigObjectFromFiles( $input->getArgument( 'config_file' ) );

		if ( $input->getOption( 'dump-config' ) ) {
			$result = json_encode( $configObject, JSON_PRETTY_PRINT );
			if ( $result === false ) {
				throw new \RuntimeException( sprintf( "Failed to get JSON representation of: %s",
					var_export( $configObject, true ) ) );
			}
			$output->writeln( $result );
		}

		$schema = ( new SchemaLoader( new SimpleFileFetcher() ) )->loadSchema( $input->getOption( 'schema' ) );
		$validator = new Validator();

		$validator->validate( $configObject, $schema );
		if ( $validator->isValid() ) {
			return self::RETURN_CODE_OK;
		}

		foreach ( $validator->getErrors() as $error ) {
			$output->writeln( ValidationErrorRenderer::render( $error ) );
		}

		return self::RETURN_CODE_ERROR;
	}

	private function loadConfigObjectFromFiles( array $configFiles ): \stdClass {
		$configReader = new ConfigReader(
			new SimpleFileFetcher(),
			...$configFiles
		);

		return $configReader->getConfigObject();
	}
}
