<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;
use WMDE\Fundraising\Frontend\App\MailTemplateFixtures\MailTemplateFixtures;
use WMDE\Fundraising\Frontend\App\MailTemplateFixtures\TemplateSettings;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailFormatter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\MailContentProviderStub;
use WMDE\Fundraising\Frontend\Tests\TestEnvironmentBootstrapper;

/**
 * This is for testing the mail template output, no classes are covered
 *
 * @coversNothing
 */
class MailTemplatesTest extends KernelTestCase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::createMissingTestFiles();
	}

	private static function createMissingTestFiles(): void {
		$mailerTwig = self::newFactory()->getMailerTwig();
		foreach ( self::getOutputTemplateAndContext() as $outputFile => $templateTestData ) {
			if ( !file_exists( $outputFile ) ) {
				file_put_contents( $outputFile, self::getFreshlyRenderedContentForOneTemplate( $templateTestData, $mailerTwig ) );
			}
		}
	}

	/**
	 * @return iterable<string, TemplateSettings>
	 */
	private static function getOutputTemplateAndContext(): iterable {
		foreach ( MailTemplateFixtures::getTemplates() as $templateTestData ) {
				yield self::getTestFilePath( $templateTestData->id ) => $templateTestData;
		}
	}

	private static function newFactory(): FunFunFactory {
		static::bootKernel();
		$bootstrapper = static::getContainer()->get( EnvironmentBootstrapper::class );

		if ( !( $bootstrapper instanceof TestEnvironmentBootstrapper ) ) {
			throw new \LogicException( 'We need to use TestEnvironmentBootstrapper to be able to override the configuration' );
		}

		$bootstrapper->overrideConfiguration( self::getConfigOverrides() );

		$ffFactory = static::getContainer()->get( FunFunFactory::class );
		$ffFactory->setContentProvider( new MailContentProviderStub() );
		$ffFactory->setMailTranslator( new FakeTranslator() );
		$ffFactory->setLocale( 'de_DE' );

		return $ffFactory;
	}

	private static function getConfigOverrides(): array {
		return [
			'twig' => [
				'strict-variables' => true
			]
		];
	}

	private static function getFreshlyRenderedContentForOneTemplate( TemplateSettings $templateData, Environment $mailerTwig ): string {
		return MailFormatter::format( $mailerTwig->render( $templateData->templateName, $templateData->templateData ) );
	}

	private static function getTestFilePath( string $id ): string {
		return __DIR__ . '/../Data/GeneratedMailTemplates/' . $id . '.txt';
	}

	/**
	 * @dataProvider storedRenderedContentProvider
	 */
	public function testCurrentRenderingMatchesStoredRendering( string $testFilePath, TemplateSettings $templateData ): void {
		$mailerTwig = self::newFactory()->getMailerTwig();
		$expectedContent = file_get_contents( $testFilePath );

		$actualContent = self::getFreshlyRenderedContentForOneTemplate( $templateData, $mailerTwig );

		$this->assertSame(
			$expectedContent,
			$actualContent,
			sprintf(
				'Template %s did not render the same output as expected, see reference file for expected output. Template Variables: %s',
				$templateData->templateName,
				var_export( $templateData->templateData, true )
			)
		);
	}

	public static function storedRenderedContentProvider(): \Iterator {
		foreach ( self::getOutputTemplateAndContext() as $testFile => $templateData ) {
			yield $testFile => [ $testFile, $templateData ];
		}
	}

}
