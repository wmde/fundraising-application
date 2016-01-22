<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ValidateEmail;

use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase;
use WMDE\Fundraising\Frontend\Validation\MailValidator;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidateEmailUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidEmail_trueIsReturned() {
		$useCase = new ValidateEmailUseCase( new MailValidator( new NullDomainNameValidator() ) );
		$this->assertTrue( $useCase->validateEmail( 'christoph.fischer@wikimedia.de' ) );
	}

	public function testGivenInvalidEmail_falseIsReturned() {
		$useCase = new ValidateEmailUseCase( new MailValidator( new NullDomainNameValidator() ) );

		$this->assertFalse( $useCase->validateEmail( '~=[,,_,,]:3' ) );
	}

}
