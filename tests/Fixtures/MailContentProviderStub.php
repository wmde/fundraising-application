<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\ContentProvider\ContentProvider;

class MailContentProviderStub implements ContentProvider {

	public function getMail( string $name, array $context = [] ): string {
		// TODO add brackets and context
		return $name;
	}

	public function getWeb( string $name, array $context = [] ): string {
		throw new \LogicException( 'Web content not implemented!' );
	}

}
