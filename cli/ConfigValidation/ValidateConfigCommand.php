<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli\ConfigValidation;

use FileFetcher\SimpleFileFetcher;
use League\JsonGuard\Validator as JSONSchemaValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidateConfigCommand extends Command {

	const NAME = 'validate-config';
	const DEFAULT_SCHEMA = __DIR__ . '/../../app/config/schema.json';
	const ERROR_RETURN_CODE = 1;
	const OK_RETURN_CODE = 0;

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
			$output->writeln( json_encode( $configObject, JSON_PRETTY_PRINT ) );
		}

		$schema = ( new SchemaLoader( new SimpleFileFetcher() ) )->loadSchema( $input->getOption( 'schema' ) );

		$validator = new JSONSchemaValidator(
			$configObject,
			$schema
		);

		if ( $validator->passes() ) {
			return self::OK_RETURN_CODE;
		}

		$renderer = new ValidationErrorRenderer( $schema );
		foreach ( $validator->errors() as $error ) {
			$output->writeln( $renderer->render( $error ) );
		}

		return self::ERROR_RETURN_CODE;
	}

	/**
	 * @throws \RuntimeException
	 * @param array $configFiles
	 * @return \stdClass
	 */
	private function loadConfigObjectFromFiles( array $configFiles ): \stdClass {
		$configReader = new ConfigReader(
			new SimpleFileFetcher(),
			...$configFiles
		);

		return $configReader->getConfigObject();
	}

	private function writeError( OutputInterface $output, string $message ) {
		$errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
		$errOutput->writeln( $message );
	}

}