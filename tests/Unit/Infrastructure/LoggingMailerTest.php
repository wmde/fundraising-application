<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use WMDE\Fundraising\Frontend\Infrastructure\LoggingMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateMailerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoggingMailerTest extends TestCase {

	public function testImplementsInterface(): void {
		$class = new ReflectionClass( LoggingMailer::class );
		$this->assertTrue( $class->implementsInterface( TemplateMailerInterface::class ) );
	}
}
