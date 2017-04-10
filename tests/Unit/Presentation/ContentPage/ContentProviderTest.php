<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation\ContentPage;

use WMDE\Fundraising\Frontend\Presentation\ContentPage\ContentNotFoundException;
use WMDE\Fundraising\Frontend\Presentation\ContentPage\ContentProvider;
use WMDE\Fundraising\HtmlFilter\PurifierInterface;
use PHPUnit\Framework\TestCase;

class ContentProviderTest extends TestCase {
	/**
	 * @var \Twig_Environment|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $env;

	/**
	 * @var PurifierInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $purifier;

	public function setUp() {
		$this->env = $this->getMockBuilder( \Twig_Environment::class )
				->disableOriginalConstructor()
				->setMethods( [ 'render' ] )
				->getMock();

		$this->purifier = $this->createMock( PurifierInterface::class );
		$this->purifier->method( 'purify' )->willReturnArgument( 0 );
	}

	public function testPageFound_ReturnsString(): void {
		$this->env->method( 'render' )->willReturn( 'ipsum' );

		$provider = new ContentProvider( $this->env, $this->purifier );
		$this->assertSame( 'ipsum', $provider->render( 'lorem' ) );
	}

	public function testPageNotFound_ThrowsException(): void {
		$exception = new \Twig_Error_Loader( 'template not found' );
		$this->env->method( 'render' )->willThrowException( $exception );

		$provider = new ContentProvider( $this->env, $this->purifier );

		$this->expectException( ContentNotFoundException::class );
		$provider->render( 'something' );
	}
}
