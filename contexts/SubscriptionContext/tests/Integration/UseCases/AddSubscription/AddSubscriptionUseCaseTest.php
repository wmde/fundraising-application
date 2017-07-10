<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\SubscriptionContext\Tests\Integration\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\EmailAddress;
use WMDE\Fundraising\Frontend\SubscriptionContext\Domain\Repositories\SubscriptionRepository;
use WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription\SubscriptionRequest;
use WMDE\Fundraising\Frontend\SubscriptionContext\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailedValidationResult;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionClass;

/**
 * @covers \WMDE\Fundraising\Frontend\SubscriptionContext\UseCases\AddSubscription\AddSubscriptionUseCase
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCaseTest extends TestCase {

	private const A_SPECIFIC_EMAIL_ADDRESS = 'curious@nyancat.com';

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

	public function setUp(): void {
		$this->repo = $this->createMock( SubscriptionRepository::class );

		$this->validator = $this->getMockBuilder( SubscriptionValidator::class )
			->disableOriginalConstructor()
			->getMock();

		$this->mailer = $this->getMockBuilder( TemplateBasedMailer::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function createValidSubscriptionRequest(): SubscriptionRequest {
		$request = new SubscriptionRequest();
		$request->setEmail( self::A_SPECIFIC_EMAIL_ADDRESS );
		return $request;
	}

	public function testGivenValidData_aSuccessResponseIsCreated(): void {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$result = $useCase->addSubscription( $this->createValidSubscriptionRequest() );
		$this->assertTrue( $result->isSuccessful() );
	}

	public function testGivenInvalidData_anErrorResponseTypeIsCreated(): void {
		$this->validator->method( 'validate' )->willReturn( new FailedValidationResult() );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->createMock( SubscriptionRequest::class );
		$result = $useCase->addSubscription( $request );
		$this->assertFalse( $result->isSuccessful() );
	}

	public function testGivenValidData_requestWillBeStored(): void {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->repo->expects( $this->once() )
			->method( 'storeSubscription' )
			->with( $this->isInstanceOf( Subscription::class ) );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$useCase->addSubscription( $this->createValidSubscriptionRequest() );
	}

	public function testGivenDataThatNeedsToBeModerated_requestWillBeStored(): void {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->validator->method( 'needsModeration' )->willReturn( true );
		$this->repo->expects( $this->once() )
			->method( 'storeSubscription' )
			->with( $this->callback( function( Subscription $subscription ) {
					return $subscription->needsModeration();
			} ) );

		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$useCase->addSubscription( $this->createValidSubscriptionRequest() );
	}

	public function testGivenInvalidData_requestWillNotBeStored(): void {
		$this->validator->method( 'validate' )->willReturn( new FailedValidationResult() );
		$this->repo->expects( $this->never() )->method( 'storeSubscription' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->createMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}

	public function testGivenValidData_requestWillBeMailed(): void {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->mailer->expects( $this->once() )
			->method( 'sendMail' )
			->with(
				$this->equalTo( new EmailAddress( self::A_SPECIFIC_EMAIL_ADDRESS ) ),
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

	public function testGivenInvalidData_requestWillNotBeMailed(): void {
		$this->validator->method( 'validate' )->willReturn( new FailedValidationResult() );
		$this->mailer->expects( $this->never() )->method( 'sendMail' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->createMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}

	public function testGivenDataThatNeedsToBeModerated_requestNotBeMailed(): void {
		$this->validator->method( 'validate' )->willReturn( new ValidationResult() );
		$this->validator->method( 'needsModeration' )->willReturn( true );
		$this->mailer->expects( $this->never() )->method( 'sendMail' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->mailer );
		$request = $this->createMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}

}
