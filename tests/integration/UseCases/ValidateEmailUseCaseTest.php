<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use WMDE\Fundraising\Frontend\UseCases\ValidateEmailUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ValidateEmailUseCase
 *
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class ValidateEmailUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidEmail_trueIsReturned() {
		$this->assertTrue( ( new ValidateEmailUseCase() )->validateEmail( 'christoph.fischer@wikimedia.de' ) );
	}

	public function testGivenInvalidEmail_falseIsReturned() {
		$this->assertFalse( ( new ValidateEmailUseCase() )->validateEmail( '~=[,,_,,]:3' ) );
	}

}
