<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateMailerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TemplateBasedMailerTest extends TestCase {

	public function testImplementsInterface(): void {
		$class = new ReflectionClass( TemplateBasedMailer::class );
		$this->assertTrue( $class->implementsInterface( TemplateMailerInterface::class ) );
	}
}
