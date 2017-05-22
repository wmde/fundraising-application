<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli;

use FileFetcher\SimpleFileFetcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use Twig_Error;

/**
 * A temporary command to dump all the mail templates
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class RenderMailTemplatesCommand extends Command {

	const NAME = 'dump-mail-tpl';

	protected function configure() {
		$this->setName( self::NAME )
			->setDescription( 'Dump rendered Mail_* Twig templates' )
			->setDefinition(
				new InputDefinition( [
					new InputOption(
						'output-path',
						'o',
						InputOption::VALUE_REQUIRED,
						'Output path for rendered text'
					),
				] )
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		/** @var FunFunFactory $ffFactory */
		$ffFactory = call_user_func( function() {
			$prodConfigPath = __DIR__ . '/../app/config/config.prod.json';

			$configReader = new ConfigReader(
				new SimpleFileFetcher(),
				__DIR__ . '/../app/config/config.dist.json',
				is_readable( $prodConfigPath ) ? $prodConfigPath : null
			);

			return new FunFunFactory( $configReader->getConfig() );
		} );

		$app = require __DIR__ . '/../app/bootstrap.php';

		$app->flush();

		$ffFactory->setTwigEnvironment( $app['twig'] );

		$twig = $ffFactory->getTwig();

		$testData = require __DIR__ . '/../tests/Data/mail_templates.php';

		$outputPath = $input->getOption( 'output-path' ) ?? '';
		if ( $outputPath && substr( $outputPath, -1 ) !== '/' ) {
			$outputPath .= '/';
		}

		foreach( $testData as $template => $config ) {

			if ( empty( $config['variants'] ) ) {
				$config['variants'] = [ '' => [] ];
			}

			foreach( $config['variants'] as $variantName => $additionalContext ) {
				$outputName =
					$outputPath .
					basename( $template, '.txt.twig' ) .
					( $variantName ? ".$variantName" : '' ) .
					'.txt'
				;

				$output->write( "$outputName" );
				if ( file_exists( $outputName ) ) {
					$output->writeln( "$outputName already exists, skipping ..." );
					continue;
				}

				try {
					file_put_contents( $outputName, $twig->render( $template, array_merge(
						$config['context'],
						$config['variants'][$variantName]
					) ) );
				} catch( Twig_Error $e ) {
					$output->writeln( '' );
					$output->writeln( '<error>' . $e->getMessage() . '</error>' );
					$output->writeln( var_export( $e->getSourceContext(), true ) );
				}
				$output->writeln( '' );
			}

		}

	}

}