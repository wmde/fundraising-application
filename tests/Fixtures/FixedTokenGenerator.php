<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\Authentication\Token;
use WMDE\Fundraising\Frontend\Authentication\TokenGenerator;

class FixedTokenGenerator implements TokenGenerator {
	public const DEFAULT_TOKEN = '1337c0ffee';

	private Token $token;

	public function __construct( ?Token $token = null ) {
		$this->token = $token ?? Token::fromHex( self::DEFAULT_TOKEN );
	}

	public function generateToken(): Token {
		return $this->token;
	}
}
