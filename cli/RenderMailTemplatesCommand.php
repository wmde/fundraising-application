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
use WMDE\Fundraising\Frontend\App\MailTemplateFixtures\MailTemplateFixtures;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailFormatter;

/**
 * A command to check and dump mail templates
 *
 * The most useful way to apply this is probably to...
 * - run this once before starting the work on a feature that touches emails, dumping the rendered templates
 * - run it again after the changes, dumping to another folder
 * - then diffing the resulting folders
 */
class RenderMailTemplatesCommand extends Command {

	private const NAME = 'app:dump-mail-templates';

	public function __construct( private readonly FunFunFactory $ffFactory ) {
		parent::__construct( self::NAME );
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
						'Output path for rendered text',
						'rendered-mail-templates'
					),
					new InputOption(
						'locale',
						'l',
						InputOption::VALUE_REQUIRED,
						'Locale to use for rendering',
						'de_DE'
					)
				] )
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$hasErrors = $this->validateTemplateFixtures(
			$this->getConfiguredTemplates(),
			iterator_to_array( $this->ffFactory->newMailTemplateFilenameTraversable() ),
			$output
		);

		$outputPath = $input->getOption( 'output-path' ) ?? '';
		if ( $outputPath && !str_ends_with( $outputPath, '/' ) ) {
			$outputPath .= '/';
		}
		if ( !file_exists( $outputPath ) ) {
			mkdir( $outputPath, 0777, true );
		}

		$this->ffFactory->setLocale( $input->getOption( 'locale' ) );

		$twig = $this->ffFactory->getMailerTwig();
		$twig->enableStrictVariables();
		$hasErrors = $this->renderTemplates( $twig, $outputPath, $output ) || $hasErrors;

		return $hasErrors ? 1 : 0;
	}

	/**
	 * Check that there are templates for all fixtures and (even more important) vice-versa
	 *
	 * @param array $testTemplateNames Template names from fixture
	 * @param array $mailTemplatePaths
	 * @param OutputInterface $output Command output
	 * @return bool
	 */
	private function validateTemplateFixtures( array $testTemplateNames, array $mailTemplatePaths, OutputInterface $output ): bool {
		$hasErrors = false;

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
	 * @param Environment $twig T
	 * he templating engine to render the templates
	 * @param string $outputPath Path where rendered templates will be written to
	 * @param OutputInterface $output Command output
	 * @return bool
	 */
	private function renderTemplates( Environment $twig, string $outputPath, OutputInterface $output ): bool {
		$hasErrors = false;
		foreach ( MailTemplateFixtures::getTemplates() as $templateSettings ) {

			$outputName = $outputPath . $templateSettings->id . '.txt';
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
							$templateSettings->templateName,
							$templateSettings->templateData
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
		return $hasErrors;
	}

	/**
	 * Get all template names configured in fixtures
	 *
	 * @return string[]
	 */
	private function getConfiguredTemplates(): array {
		$templates = [];
		foreach ( MailTemplateFixtures::getTemplateProviders() as $templateProvider ) {
			$templates[] = $templateProvider->getTemplateName();
		}
		return $templates;
	}
}
