<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ConstraintViolation;
use WMDE\FunValidators\ValidationResponse;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter
 *
 * @license GPL-2.0-or-later
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionHtmlPresenterTest extends TestCase {

	public function testGivenSuccessResponse_templateIsRenderedWithoutMessages(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( [] );
		$presenter = new ConfirmSubscriptionHtmlPresenter( $twig );
		$presenter->present( ValidationResponse::newSuccessResponse() );
	}

	public function testGivenFailureResponse_templateIsRenderedWithoutMessages(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( [ 'error_message' => 'The confirmation code has expired.' ] );
		$presenter = new ConfirmSubscriptionHtmlPresenter( $twig );
		$constraintViolation = new ConstraintViolation( 'deadbeef', 'The confirmation code has expired.' );
		$presenter->present( ValidationResponse::newFailureResponse( [ $constraintViolation ] ) );
	}

}
