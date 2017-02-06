<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Validation;

use Twig_Environment;
use Twig_Error_Loader;
use WMDE\Fundraising\Frontend\Validation\TemplateNameValidator;

class TemplateNameValidatorTest extends \PHPUnit\Framework\TestCase {

	public function testWhenEnvironmentThrowsNoException_validationSuccceeds() {
		$twig = $this->getMockBuilder( Twig_Environment::class )->disableOriginalConstructor()->getMock();
		$validator = new TemplateNameValidator( $twig );
		$this->assertTrue( $validator->validate( 'Unicorns' )->isSuccessful() );
	}

	public function testWhenEnvironmentThrowsLoadException_validationFails() {
		$twig = $this->getMockBuilder( Twig_Environment::class )->disableOriginalConstructor()->getMock();
		$twig->method( 'loadTemplate' )->willThrowException( new Twig_Error_Loader( 'That template was not found' ) );
		$validator = new TemplateNameValidator( $twig );
		$this->assertFalse( $validator->validate( 'Rainbows' )->isSuccessful() );
	}

}
