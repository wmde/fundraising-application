<?php

namespace WMDE\Fundraising\Frontend\UseCases\ValidateEmail;

use WMDE\Fundraising\Frontend\MailValidator;

class ValidateEmailUseCase {

	public function validateEmail( string $email ): bool {
		return ( new MailValidator( MailValidator::TEST_WITH_MX ) )->validateMail( $email );
	}

}
