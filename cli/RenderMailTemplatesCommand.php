<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli;

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;
use FileFetcher\SimpleFileFetcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig_Environment;
use Twig_Error;

/**
 * A command to check and dump mail templates
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

		$config = $this->getDefaultConfig();
		$config['twig']['strict-variables'] = true;

		$ffFactory = new FunFunFactory( $config );

		$app = require __DIR__ . '/../app/bootstrap.php';

		$app->flush();

		$ffFactory->setTwigEnvironment( $app['twig'] );

		$testData = require __DIR__ . '/../tests/Data/mail_templates.php';

		$this->validateTemplateFixtures( $testData, $config['twig']['loaders']['filesystem']['template-dir'], $output );

		$outputPath = $input->getOption( 'output-path' ) ?? '';
		if ( $outputPath && substr( $outputPath, -1 ) !== '/' ) {
			$outputPath .= '/';
		}

		$this->renderTemplates( $testData, $ffFactory->getTwig(), $outputPath, $output );
	}

	private function getDefaultConfig(): array {
		$prodConfigPath = __DIR__ . '/../app/config/config.prod.json';

		$configReader = new ConfigReader(
			new SimpleFileFetcher(),
			__DIR__ . '/../app/config/config.dist.json',
			is_readable( $prodConfigPath ) ? $prodConfigPath : null
		);

		return $configReader->getConfig();
	}

	/**
	 * Check that there are templates for all fixtures and (even more important) vice-versa
	 *
	 * @param array $testData Template names and fixture information to render these templates
	 * @param array $mailTemplatePaths Directories containing templates
	 * @param OutputInterface $output Command output
	 */
	private function validateTemplateFixtures( array $testData, array $mailTemplatePaths, OutputInterface $output ): void {
		$mailTemplatesOnDisk = [];
		foreach ( $mailTemplatePaths as $path ) {
			$mailFilesInFolder = glob( $path . '/Mail_*' );
			array_walk( $mailFilesInFolder, function( & $filename ) {
				$filename = basename( $filename ); // this would cause problems w/ mail templates in sub-folders
			} );
			$mailTemplatesOnDisk = array_merge( $mailTemplatesOnDisk, $mailFilesInFolder );
		}

		$testTemplateNames = array_keys( $testData );

		$untestedTemplates = array_diff( $mailTemplatesOnDisk, $testTemplateNames );
		if ( count( $untestedTemplates ) ) {
			$output->writeln(
				'<error>There are untested templates: ' . implode( ', ', $untestedTemplates ) . '</error>'
			);
		}

		$strayTemplates = array_diff( $testTemplateNames, $mailTemplatesOnDisk );
		if ( count( $strayTemplates ) ) {
			$output->writeln(
				'<error>There are tests for non-existing templates: ' . implode( ', ', $strayTemplates ) . '</error>'
			);
		}
	}

	/**
	 * Render all templates and write them to disk to allow a comparison with an alternative data set
	 *
	 * @param array $testData Template names and fixture information to render these templates
	 * @param Twig_Environment $twig The templating engine to render the templates
	 * @param string $outputPath Path where rendered templates will be written to
	 * @param OutputInterface $output Command output
	 */
	private function renderTemplates( array $testData, Twig_Environment $twig, string $outputPath, OutputInterface $output ): void {
		foreach( $testData as $template => $templateSettings ) {

			if ( empty( $templateSettings['variants'] ) ) {
				$templateSettings['variants'] = [ '' => [] ];
			}

			foreach( $templateSettings['variants'] as $variantName => $additionalContext ) {
				$outputName =
					$outputPath .
					basename( $template, '.txt.twig' ) .
					( $variantName ? ".$variantName" : '' ) .
					'.txt';

				$output->write( "$outputName" );
				if ( file_exists( $outputName ) ) {
					$output->writeln( "$outputName already exists, skipping ..." );
					continue;
				}

				try {
					file_put_contents( $outputName, $twig->render( $template, array_merge_recursive(
						$templateSettings['context'],
						$templateSettings['variants'][$variantName]
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
