<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration;

use WMDE\Fundraising\Frontend\TwigFactory;

class TwigFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testTwigInstanceUsesDollarPlaceholdersForVariables() {
		$twig = TwigFactory::newFromConfig( [
			'enable-twig-cache' => false,
			'template-dir' => __DIR__ . '/../templates'
		] );
		$result = $twig->render( 'variableReplacement.twig', [ 'testvar' => 'Meeow!' ] );
		$this->assertSame( 'Meeow!', $result);
	}
}
