<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use Symfony\Component\Translation\Loader\ArrayLoader;
use WMDE\Fundraising\Frontend\TranslationFactory;

class TranslationFactoryTest extends \PHPUnit_Framework_TestCase {
	public function testLoadersAreSet(){
		$factory = new TranslationFactory();
		$loader = new ArrayLoader();
		$translator = $factory->create( ['array' => $loader] );
		$translator->addResource( 'array', ['foo' => 'bar'], 'de_DE' );
		$this->assertSame( 'bar', $translator->trans( 'foo') );
	}
}
