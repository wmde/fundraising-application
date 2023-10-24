<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\PaymentContext\Services\PayPal\PayPalPaymentProviderAdapterConfigReader;

class PaypalAPIConfigValidation extends Command {
	private const NAME = 'app:validate:paypal-config';

	protected function configure(): void {
		$this->setName( self::NAME )
			->setDescription( 'Validate PayPal configuration for subscription plans' )
			->addArgument( 'config', InputArgument::REQUIRED, 'Path to the configuration file' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$configPath = $input->getArgument( 'config' );
		if ( !is_readable( $configPath ) ) {
			$output->writeln( "<error>File '$configPath' not found or not readable.</error>" );
			return Command::FAILURE;
		}
		try {
			PayPalPaymentProviderAdapterConfigReader::readConfig( $configPath );
		} catch ( \Exception $e ) {
			$output->writeln( $e->getMessage() );
			return Command::FAILURE;
		}

		$output->writeln( "PayPal config file '$configPath' is valid.", OutputInterface::VERBOSITY_VERBOSE | OutputInterface::OUTPUT_NORMAL );

		return Command::SUCCESS;
	}

}
