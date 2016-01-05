<?php

namespace WMDE\Fundraising\Frontend\UseCases\ValidateEmail;

use WMDE\Fundraising\Frontend\MailValidator;

class ValidateEmailUseCase {

	/**
	 * @param string $email
	 *
	 * @return bool
	 */
	public function validateEmail( $email ) {
		return ( new MailValidator( MailValidator::TEST_WITH_MX ) )->validateMail( $email );
	}

}
