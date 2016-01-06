<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ValidateEmail;

use WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidateEmailUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidEmail_trueIsReturned() {
		$this->assertTrue( ( new ValidateEmailUseCase() )->validateEmail( 'christoph.fischer@wikimedia.de' ) );
	}

	public function testGivenInvalidEmail_falseIsReturned() {
		$this->assertFalse( ( new ValidateEmailUseCase() )->validateEmail( '~=[,,_,,]:3' ) );
	}

}
