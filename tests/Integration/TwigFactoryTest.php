<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration;

use WMDE\Fundraising\Frontend\Domain\PageRetriever;
use WMDE\Fundraising\Frontend\TwigFactory;

class TwigFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$pageRetriever = $this->getMock( PageRetriever::class );
		$twig = TwigFactory::newFromConfig( [
			'enable-twig-cache' => false,
			'template-dir' => __DIR__ . '/../templates'
		], $pageRetriever );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result);
	}

	public function testTwigInstancesCanLoadTemplatesFromWiki() {
		$pageRetriever = $this->getMock( PageRetriever::class );
		$pageRetriever->method( 'fetchPage' )->willReturn( 'Meeow!' );
		$twig = TwigFactory::newFromConfig( [
			'enable-twig-cache' => false,
			'template-dir' => __DIR__ . '/../templates'
		], $pageRetriever );
		$result = $twig->render( 'Canis_silvestris' );
		$this->assertSame( 'Meeow!', $result);
	}

	public function testFileTemplatesArePreferredOverWikiPages() {
		$pageRetriever = $this->getMock( PageRetriever::class );
		$pageRetriever->method( 'fetchPage' )->willReturn( 'Woof?' );
		$twig = TwigFactory::newFromConfig( [
			'enable-twig-cache' => false,
			'template-dir' => __DIR__ . '/../templates'
		], $pageRetriever );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result);
	}
}
