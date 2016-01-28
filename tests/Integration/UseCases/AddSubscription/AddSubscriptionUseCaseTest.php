<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddSubscription;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\Validation\SubscriptionValidator;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;

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

	public function setUp() {
		parent::setUp();
		$this->repo = $this->getMock( SubscriptionRepository::class );
		$this->validator = $this->getMockBuilder( SubscriptionValidator::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testGivenValidData_aSuccessResponseIsCreated() {
		$this->validator->method( 'validate' )->willReturn( true );
		$usecase = new AddSubscriptionUseCase( $this->repo, $this->validator );
		$request = $this->getMock( SubscriptionRequest::class );
		$result = $usecase->addSubscription( $request );
		$this->assertTrue( $result->isSuccessful() );
	}

	public function testGivenInvalidData_anErrorResponseTypeIsCreated() {
		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getConstraintViolations' )->willReturn( [ 'dummyConstraintViolation' ] );
		$usecase = new AddSubscriptionUseCase( $this->repo, $this->validator );
		$request = $this->getMock( SubscriptionRequest::class );
		$result = $usecase->addSubscription( $request );
		$this->assertFalse( $result->isSuccessful() );
	}

	public function testGivenValidData_requestWillBeStored() {
		$this->validator->method( 'validate' )->willReturn( true );
		$this->repo->expects( $this->once() )
			->method( 'storeSubscription' )
			->with( $this->isInstanceOf( Subscription::class ) );
		$usecase = new AddSubscriptionUseCase( $this->repo, $this->validator );
		$request = $this->getMock( SubscriptionRequest::class );
		$usecase->addSubscription( $request );
	}

	public function testGivenInvalidData_requestWillNotBeStored() {
		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getConstraintViolations' )->willReturn( [] );
		$this->repo->expects( $this->never() )->method( 'store' );
		$usecase = new AddSubscriptionUseCase( $this->repo, $this->validator );
		$request = $this->getMock( SubscriptionRequest::class );
		$usecase->addSubscription( $request );
	}
}