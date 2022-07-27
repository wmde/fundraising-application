<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Error\Error;
use WMDE\Fundraising\Frontend\App\MailTemplates;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailFormatter;

/**
 * A command to check and dump mail templates
 *
 * The most useful way to apply this is probably to...
 * - run this once before starting the work on a feature that touches emails, dumping the rendered templates
 * - run it again after the changes, dumping to another folder
 * - then diffing the resulting folders
 *
 * @license GPL-2.0-or-later
 */
class RenderMailTemplatesCommand extends Command {

	private const NAME = 'app:dump-mail-templates';

	private FunFunFactory $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		parent::__construct( self::NAME );
		$this->ffFactory = $ffFactory;
		$this->ffFactory->setLocale( "de_DE" );
	}

	protected function configure(): void {
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

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$mailTemplates = new MailTemplates( $this->ffFactory );
		$testData = $mailTemplates->get();

		$hasErrors = $this->validateTemplateFixtures(
			$testData,
			iterator_to_array( $this->ffFactory->newMailTemplateFilenameTraversable() ),
			$output
		);

		$outputPath = $input->getOption( 'output-path' ) ?? '';
		if ( $outputPath && substr( $outputPath, -1 ) !== '/' ) {
			$outputPath .= '/';
		}

		$twig = $this->ffFactory->getMailerTwig();
		$twig->enableStrictVariables();
		$hasErrors = $this->renderTemplates( $testData, $twig, $outputPath, $output ) || $hasErrors;

		return $hasErrors ? 1 : 0;
	}

	/**
	 * Check that there are templates for all fixtures and (even more important) vice-versa
	 *
	 * @param array $testData Template names and fixture information to render these templates
	 * @param array $mailTemplatePaths
	 * @param OutputInterface $output Command output
	 * @return bool
	 */
	private function validateTemplateFixtures( array $testData, array $mailTemplatePaths, OutputInterface $output ): bool {
		$hasErrors = false;
		$testTemplateNames = array_keys( $testData );

		$untestedTemplates = array_diff( $mailTemplatePaths, $testTemplateNames );

		if ( !empty( $untestedTemplates ) ) {
			$hasErrors = true;
			$output->writeln(
				'<error>There are untested templates: ' . implode( ', ', $untestedTemplates ) . '</error>'
			);
		}

		$strayTemplates = array_diff( $testTemplateNames, $mailTemplatePaths );

		if ( !empty( $strayTemplates ) ) {
			$hasErrors = true;
			$output->writeln(
				'<error>There are tests for non-existing templates: ' . implode( ', ', $strayTemplates ) . '</error>'
			);
		}
		return $hasErrors;
	}

	/**
	 * Render all templates and write them to disk to allow a comparison with an alternative data set
	 *
	 * @param array $testData Template names and fixture information to render these templates
	 * @param Environment $twig The templating engine to render the templates
	 * @param string $outputPath Path where rendered templates will be written to
	 * @param OutputInterface $output Command output
	 * @return bool
	 */
	private function renderTemplates( array $testData, Environment $twig, string $outputPath, OutputInterface $output ): bool {
		$hasErrors = false;
		foreach ( $testData as $templateFileName => $templateSettings ) {

			if ( empty( $templateSettings['variants'] ) ) {
				$templateSettings['variants'] = [ '' => [] ];
			}

			foreach ( $templateSettings['variants'] as $variantName => $additionalContext ) {
				$outputName =
					$outputPath .
					basename( $templateFileName, '.txt.twig' ) .
					( $variantName ? ".$variantName" : '' ) .
					'.txt';

				$output->write( "$outputName" );
				if ( file_exists( $outputName ) ) {
					$output->writeln( "$outputName already exists, skipping ..." );
					continue;
				}

				try {
					file_put_contents(
						$outputName,
						MailFormatter::format(
							$twig->render(
								$templateFileName,
								array_merge_recursive(
									$templateSettings['context'],
									$additionalContext
								)
							)
						)
					);
				} catch ( Error $e ) {
					$hasErrors = true;
					$output->writeln( '' );
					$output->writeln( '<error>' . $e->getMessage() . '</error>' );
					$output->writeln( var_export( $e->getSourceContext(), true ) );
				}
				$output->writeln( '' );
			}
		}
		return $hasErrors;
	}
}
