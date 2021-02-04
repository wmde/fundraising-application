<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\App\MailTemplates;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\EnvironmentBootstrapper;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailFormatter;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;
use WMDE\Fundraising\Frontend\Tests\TestEnvironmentBootstrapper;

/**
 * This is for testing the mail template output, no classes are covered
 *
 * @coversNothing
 */
class MailTemplatesTest extends KernelTestCase {

	private FunFunFactory $factory;

	public function setUp(): void {
		$this->createMissingTestFiles();
	}

	private function createMissingTestFiles(): void {
		foreach ( $this->getFreshlyRenderedContent() as $testFilePath => $testFileContent ) {
			if ( !file_exists( $testFilePath ) ) {
				file_put_contents( $testFilePath, $testFileContent );
			}
		}
	}

	private function getFreshlyRenderedContent(): \Iterator {
		$this->factory = $this->newFactory();

		foreach ( $this->getTestData() as $templateFileName => $templateTestData ) {
			yield from $this->getFreshlyRenderedContentForTemplate( $templateFileName, $templateTestData );
		}
	}

	private function newFactory(): FunFunFactory {
		static::bootKernel();
		$bootstrapper = static::$container->get( EnvironmentBootstrapper::class );

		if ( !( $bootstrapper instanceof TestEnvironmentBootstrapper ) ) {
			throw new \LogicException( 'We need to use TestEnvironmentBootstrapper to be able to override the configuration' );
		}

		$bootstrapper->overrideConfiguration( $this->getConfig() );

		$ffFactory = static::$container->get( FunFunFactory::class );

		$contentProvider = $this->createMock( ContentProvider::class );
		$contentProvider->method( 'getMail' )->willReturnArgument( 0 );

		$ffFactory->setContentProvider( $contentProvider );
		$ffFactory->setMailTranslator( new FakeTranslator() );

		return $ffFactory;
	}

	private function getConfig(): array {
		return [
			'twig' => [
				'strict-variables' => true
			]
		];
	}

	private function getTestData(): array {
		$mailTemplates = new MailTemplates( $this->factory );
		return $mailTemplates->get();
	}

	private function getFreshlyRenderedContentForTemplate( string $templateFileName, array $templateTestData ): \Iterator {
		if ( empty( $templateTestData['variants'] ) ) {
			$templateTestData['variants'] = [ '' => [] ];
		}

		foreach ( $templateTestData['variants'] as $variantName => $additionalContext ) {
			$filePath = $this->createTestFilePath( $templateFileName, $variantName );

			$content = MailFormatter::format( $this->factory->getMailerTwig()->render(
				$templateFileName,
				array_merge_recursive(
					$templateTestData['context'],
					$additionalContext
				)
			) );

			yield $filePath => $content;
		}
	}

	private function createTestFilePath( string $templateFileName, string $variantName ): string {
		return __DIR__ . '/../Data/GeneratedMailTemplates/'
			. basename( $templateFileName, '.txt.twig' )
			. ( $variantName === '' ? '' : ".$variantName" )
			. '.txt';
	}

	/**
	 * @dataProvider storedRenderedContentProvider
	 */
	public function testCurrentRenderingMatchesStoredRendering( string $testFilePath, string $testFileContent ): void {
		$this->assertSame(
			file_get_contents( $testFilePath ),
			$testFileContent
		);
	}

	public function storedRenderedContentProvider(): \Iterator {
		foreach ( $this->getFreshlyRenderedContent() as $testFilePath => $testFileContent ) {
			yield $testFilePath => [ $testFilePath, $testFileContent ];
		}
	}

}
