<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\API\Donation;

use WMDE\Fundraising\DonationContext\UseCases\AddComment\AddCommentRequest;

class FrameworkAddCommentRequest {
	public function __construct(
		public readonly string $comment,
		public readonly int $donationId,
		public readonly string $updateToken = '',
		public readonly bool $isPublic = false,
		public readonly bool $withName = false,
	) {
	}

	public function getRequestForUseCase(): AddCommentRequest {
		return new AddCommentRequest(
			commentText: $this->comment,
			isPublic: $this->isPublic,
			isAnonymous: !$this->withName,
			donationId: $this->donationId,
		);
	}
}
