<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication;

interface TokenGenerator {
	public function generateToken(): Token;
}
