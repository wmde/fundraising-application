<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddSubscription;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Domain\RequestRepository;
use WMDE\Fundraising\Frontend\Validation\RequestValidator;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\AddSubscriptionUseCase;
use WMDE\Fundraising\Frontend\UseCases\AddSubscription\SubscriptionRequest;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionUseCaseTest extends \PHPUnit_Framework_TestCase
{
	private $repo;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|RequestValidator
	 */
	private $validator;

	public function setUp() {
		parent::setUp();
		$this->repo = $this->getMock( RequestRepository::class );
		$this->validator = $this->getMockBuilder( RequestValidator::class )
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
		$this->validator->method( 'getValidationErrors' )->willReturn( [ 'dummyConstraintViolation' ] );
		$usecase = new AddSubscriptionUseCase( $this->repo, $this->validator );
		$request = $this->getMock( SubscriptionRequest::class );
		$result = $usecase->addSubscription( $request );
		$this->assertFalse( $result->isSuccessful() );
	}

	public function testGivenValidData_requestWillBeStored() {
		$this->validator->method( 'validate' )->willReturn( true );
		$this->repo->expects( $this->once() )
			->method( 'storeRequest' )
			->with( $this->isInstanceOf( Request::class ) );
		$usecase = new AddSubscriptionUseCase( $this->repo, $this->validator );
		$request = $this->getMock( SubscriptionRequest::class );
		$usecase->addSubscription( $request );
	}

	public function testGivenInvalidData_requestWillNotBeStored() {
		$this->validator->method( 'validate' )->willReturn( false );
		$this->validator->method( 'getValidationErrors' )->willReturn( [] );
		$this->repo->expects( $this->never() )->method( 'store' );
		$usecase = new AddSubscriptionUseCase( $this->repo, $this->validator );
		$request = $this->getMock( SubscriptionRequest::class );
		$usecase->addSubscription( $request );
	}
}