<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddSubscription;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\TemplatedMessage;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;
use WMDE\Fundraising\Frontend\MailAddress;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCaseTest extends \PHPUnit_Framework_TestCase
{
	private $repo;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|SubscriptionValidator
	 */
	private $validator;

	private $messenger;

	private $message;

	public function setUp() {
		parent::setUp();
		$this->repo = $this->getMock( SubscriptionRepository::class );
		$this->validator = $this->getMockBuilder( SubscriptionValidator::class )
			->disableOriginalConstructor()
			->getMock();
		$this->messenger = $this->getMockBuilder( Messenger::class )
			->disableOriginalConstructor()
			->getMock();
		$this->message = $this->getMockBuilder( TemplatedMessage::class )
			->disableOriginalConstructor()
			->getMock();
	}

	private function createValidSubscriptionRequest(): SubscriptionRequest {
		$request = new SubscriptionRequest();
		$request->setEmail( 'curious@nyancat.com' );
		return $request;
	}

	public function testGivenValidData_aSuccessResponseIsCreated() {
		$this->validator->method( 'validate' )->willReturn( true );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->messenger, $this->message );
		$result = $useCase->addSubscription( $this->createValidSubscriptionRequest() );
		$this->assertTrue( $result->isSuccessful() );
	}

	public function testGivenInvalidData_anErrorResponseTypeIsCreated() {
		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getConstraintViolations' )->willReturn( [ 'dummyConstraintViolation' ] );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->messenger, $this->message );
		$request = $this->getMock( SubscriptionRequest::class );
		$result = $useCase->addSubscription( $request );
		$this->assertFalse( $result->isSuccessful() );
	}

	public function testGivenValidData_requestWillBeStored() {
		$this->validator->method( 'validate' )->willReturn( true );
		$this->repo->expects( $this->once() )
			->method( 'storeSubscription' )
			->with( $this->isInstanceOf( Subscription::class ) );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->messenger, $this->message );
		$useCase->addSubscription( $this->createValidSubscriptionRequest() );
	}

	public function testGivenInvalidData_requestWillNotBeStored() {
		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getConstraintViolations' )->willReturn( [] );
		$this->repo->expects( $this->never() )->method( 'storeSubscription' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->messenger, $this->message );
		$request = $this->getMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}

	public function testGivenValidData_requestWillBeMailed() {
		$this->validator->method( 'validate' )->willReturn( true );
		$this->messenger->expects( $this->once() )
			->method( 'sendMessageToUser' )
			->with( $this->isInstanceOf( TemplatedMessage::class ),
				$this->isInstanceOf( MailAddress::class )
			);
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->messenger, $this->message );
		$useCase->addSubscription( $this->createValidSubscriptionRequest() );
	}

	public function testGivenInvalidData_requestWillNotBeMailed() {
		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getConstraintViolations' )->willReturn( [] );
		$this->messenger->expects( $this->never() )->method( 'sendMessageToUser' );
		$useCase = new AddSubscriptionUseCase( $this->repo, $this->validator, $this->messenger, $this->message );
		$request = $this->getMock( SubscriptionRequest::class );
		$useCase->addSubscription( $request );
	}


}