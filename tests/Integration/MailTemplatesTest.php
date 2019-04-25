<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\App\Bootstrap;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailFormatter;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;
use WMDE\Fundraising\Frontend\App\MailTemplates;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MailTemplatesTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var FunFunFactory
	 */
	private $factory;

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
		$ffFactory = TestEnvironment::newInstance( $this->getConfig() )->getFactory();

		$contentProvider = $this->getMockBuilder( ContentProvider::class )
			->disableOriginalConstructor()
			->getMock();
		$contentProvider->method( 'getMail' )->willReturnArgument( 0 );

		$ffFactory->setContentProvider( $contentProvider );

		$app = Bootstrap::initializeApplication( $ffFactory );
		$app->flush();

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

		foreach( $templateTestData['variants'] as $variantName => $additionalContext ) {
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

