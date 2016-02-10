<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ConfirmSubscription;

use PHPUnit_Framework_MockObject_MockObject;
use WMDE\Fundraising\Entities\Address;
use WMDE\Fundraising\Entities\Subscription;
use WMDE\Fundraising\Frontend\Domain\SubscriptionRepository;
use WMDE\Fundraising\Frontend\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SubscriptionRepositorySpy;
use WMDE\Fundraising\Frontend\UseCases\ConfirmSubscription\ConfirmSubscriptionUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ConfirmSubscription\ConfirmSubscriptionUseCase
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ConfirmSubscriptionUseCaseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|SubscriptionRepository
	 */
	private $repo;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|TemplateBasedMailer
	 */
	private $mailer;

	public function setUp() {
		$this->repo = new SubscriptionRepositorySpy();

		$this->mailer = $this->getMockBuilder( TemplateBasedMailer::class )
				->disableOriginalConstructor()
				->getMock();
	}

	private function newSubscriptionAddress() {
		$address = new Address();
		$address->setSalutation( 'Herr' );
		$address->setFirstName( 'Nyan' );
		$address->setLastName( 'Cat' );
		$address->setTitle( 'Dr.' );
		return $address;
	}

	public function testGivenNoSubscriptions_anErrorResponseIsCreated() {
		$this->mailer->expects( $this->never() )->method( 'sendMail' );
		$useCase = new ConfirmSubscriptionUseCase( $this->repo, $this->mailer );
		$result = $useCase->confirmSubscription( 'deadbeef' );
		$this->assertFalse( $result->isSuccessful() );
	}

	public function testGivenASubscriptionWithWrongStatus_anErrorResponseIsCreated() {
		$subscription = new Subscription();
		$subscription->setHexConfirmationCode( 'deadbeef' );
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setAddress( $this->newSubscriptionAddress() );
		$subscription->setStatus( Subscription::STATUS_CONFIRMED );
		$this->repo->storeSubscription( $subscription );
		$this->mailer->expects( $this->never() )->method( 'sendMail' );
		$useCase = new ConfirmSubscriptionUseCase( $this->repo, $this->mailer );
		$result = $useCase->confirmSubscription( 'deadbeef' );
		$this->assertFalse( $result->isSuccessful() );
	}

	public function testGivenASubscription_aSuccessIsCreated() {
		$subscription = new Subscription();
		$subscription->setHexConfirmationCode( 'deadbeef' );
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setAddress( $this->newSubscriptionAddress() );
		$this->repo->storeSubscription( $subscription );
		$useCase = new ConfirmSubscriptionUseCase( $this->repo, $this->mailer  );
		$result = $useCase->confirmSubscription( 'deadbeef' );
		$this->assertTrue( $result->isSuccessful() );
	}

	public function testGivenASubscription_aConfirmationMailIsSent() {
		$subscription = new Subscription();
		$subscription->setHexConfirmationCode( 'deadbeef' );
		$subscription->setEmail( 'nyan@awesomecats.com' );
		$subscription->setAddress( $this->newSubscriptionAddress() );
		$this->repo->storeSubscription( $subscription );
		$this->mailer->expects( $this->once() )->method( 'sendMail' );
		$useCase = new ConfirmSubscriptionUseCase( $this->repo, $this->mailer  );
		$result = $useCase->confirmSubscription( 'deadbeef' );
		$this->assertTrue( $result->isSuccessful() );
	}
}
