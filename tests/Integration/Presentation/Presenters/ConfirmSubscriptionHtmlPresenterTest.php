<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\Presentation\Presenters;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter;
use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\FunValidators\ValidationResponse;
use WMDE\FunValidators\ConstraintViolation;

/**
 * @covers WMDE\Fundraising\Frontend\Presentation\Presenters\ConfirmSubscriptionHtmlPresenter
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionHtmlPresenterTest extends \PHPUnit\Framework\TestCase {

	public function testGivenSuccessResponse_templateIsRenderedWithoutMessages(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$translator = $this->getMockBuilder( Translator::class )->disableOriginalConstructor()->getMock();
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( [] );
		$presenter = new ConfirmSubscriptionHtmlPresenter( $twig, $translator );
		$presenter->present( ValidationResponse::newSuccessResponse() );
	}

	public function testGivenFailureResponse_templateIsRenderedWithoutMessages(): void {
		$twig = $this->getMockBuilder( TwigTemplate::class )->disableOriginalConstructor()->getMock();
		$twig->expects( $this->once() )
			->method( 'render' )
			->with( [ 'error_message' => 'The confirmation code has expired.' ] );
		$presenter = new ConfirmSubscriptionHtmlPresenter( $twig, $this->getTranslator() );
		$constraintViolation = new ConstraintViolation( 'deadbeef', 'The confirmation code has expired.' );
		$presenter->present( ValidationResponse::newFailureResponse( [ $constraintViolation ] ) );
	}

	private function getTranslator(): Translator {
		$translator = new Translator( 'en' );
		$translator->addLoader( 'array', new ArrayLoader() );
		$translator->addResource(
			'array',
			[ 'error_message' => 'The confirmation code has expired.' ],
			'en'
		);

		return $translator;
	}

}
