<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddSubscription;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailedValidationResult;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;
use WMDE\Fundraising\Frontend\MailAddress;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCaseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|SubscriptionRepository
	 */
	private $repo;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|SubscriptionValidator
	 */
	private $validator;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|TemplateBasedMailer
	 */
	private $mailer;

	public function setUp() {
		$this->repo = $this->getMock( SubscriptionRepository::class );

		$this->validator = $this->getMockBuilder( SubscriptionValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$this->mailer = $this->getMockBuilder( TemplateBasedMailer::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function createValidSubscriptionRequest(): SubscriptionRequest {
		$request = new SubscriptionRequest();
		$request->setEmail( 'curious@nyancat.com' );
		return $request;
	}

	public function testGivenValidData_aSuccessResponseIsCreated() {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$result = $useCase->addSubscription( $this->createValidSubscriptionRequest() );
		$this->assertTrue( $result->isSuccessful() );
	}

	public function testGivenInvalidData_anErrorResponseTypeIsCreated() {
		$this->validator->method( 'validate' )->willReturn( new FailedValidationResult() );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->getMock( SubscriptionRequest::class );
		$result = $useCase->addSubscription( $request );
		$this->assertFalse( $result->isSuccessful() );
	}

	public function testGivenValidData_requestWillBeStored() {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->repo->expects( $this->once() )
			->method( 'storeSubscription' )
			->with( $this->isInstanceOf( Subscription::class ) );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$useCase->addSubscription( $this->createValidSubscriptionRequest() );
	}

	public function testGivenDataThatNeedsToBeModerated_requestWillBeStored() {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->validator->method( 'needsModeration' )->willReturn( true );
		$this->repo->expects( $this->once() )
			->method( 'storeSubscription' )
			->with( $this->isInstanceOf( Subscription::class ) );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$useCase->addSubscription( $this->createValidSubscriptionRequest() );
	}

	public function testGivenInvalidData_requestWillNotBeStored() {
		$this->validator->method( 'validate' )->willReturn( new FailedValidationResult() );
		$this->repo->expects( $this->never() )->method( 'storeSubscription' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->getMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}

	public function testGivenValidData_requestWillBeMailed() {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->mailer->expects( $this->once() )
			->method( 'sendMail' )
			->with(
				$this->equalTo( new MailAddress( 'curious@nyancat.com' ) ),
				$this->callback( function( $value ) {
					$this->assertInternalType( 'array', $value );
					$this->assertArrayHasKey( 'subscription', $value );
					$this->assertInstanceOf( Subscription::class, $value['subscription'] );

					// FIXME: actual template params are not tested
					// (and some of the used data is not even in the test request model)

					return true;
				} )
			);
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$useCase->addSubscription( $this->createValidSubscriptionRequest() );
	}

	public function testGivenInvalidData_requestWillNotBeMailed() {
		$this->validator->method( 'validate' )->willReturn( new FailedValidationResult() );
		$this->mailer->expects( $this->never() )->method( 'sendMail' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->getMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}

	public function testGivenDataThatNeedsToBeModerated_requestNotBeMailed() {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->validator->method( 'needsModeration' )->willReturn( true );
		$this->mailer->expects( $this->never() )->method( 'sendMail' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->getMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}

}
