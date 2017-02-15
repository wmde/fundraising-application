<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\UseCases\AddComment;

use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddComment\AddCommentValidationResult as Result;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddCommentValidator {

	private const MAX_NAME_LENGTH = 150;
	private const MAX_COMMENT_LENGTH = 2048;

	public function validate( AddCommentRequest $request ): Result {
		$violations = [];

		if ( strlen( $request->getCommentText() ) > self::MAX_COMMENT_LENGTH ) {
			$violations[Result::SOURCE_COMMENT] = Result::VIOLATION_COMMENT_TOO_LONG;
		}

		if ( strlen( $request->getAuthorDisplayName() ) > self::MAX_NAME_LENGTH ) {
			$violations[Result::SOURCE_NAME] = Result::VIOLATION_NAME_TOO_LONG;
		}

		return new Result( $violations );
	}
}