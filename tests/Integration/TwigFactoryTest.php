<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration;

use WMDE\Fundraising\Frontend\Presenters\Content\WikiContentProvider;
use WMDE\Fundraising\Frontend\TwigFactory;

class TwigFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$contentProvider = $this->getMockBuilder( WikiContentProvider::class )
			->disableOriginalConstructor()->getMock();
		$twig = TwigFactory::newFromConfig( [
			'enable-twig-cache' => false,
			'template-dir' => __DIR__ . '/../templates'
		], $contentProvider );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result);
	}

	public function testTwigInstancesCanLoadTemplatesFromWiki() {
		$contentProvider = $this->getMockBuilder( WikiContentProvider::class )
			->disableOriginalConstructor()->getMock();
		$contentProvider->method( 'getContent' )->willReturn( 'Meeow!' );
		$twig = TwigFactory::newFromConfig( [
			'enable-twig-cache' => false,
			'template-dir' => __DIR__ . '/../templates'
		], $contentProvider );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result);
	}

	public function testFileTemplatesArePreferredOverWikiPages() {
		$contentProvider = $this->getMockBuilder( WikiContentProvider::class )
			->disableOriginalConstructor()->getMock();
		$contentProvider->method( 'getContent' )->willReturn( 'Woof?' );
		$twig = TwigFactory::newFromConfig( [
			'enable-twig-cache' => false,
			'template-dir' => __DIR__ . '/../templates'
		], $contentProvider );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result);
	}
}
