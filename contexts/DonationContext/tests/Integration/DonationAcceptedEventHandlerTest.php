<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration;

use WMDE\Fundraising\Frontend\DonationContext\Authorization\DonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\DonationAcceptedEventHandler;
use WMDE\Fundraising\Frontend\DonationContext\Infrastructure\DonationConfirmationMailer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\DonationAcceptedEventHandler
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationAcceptedEventHandlerTest extends \PHPUnit\Framework\TestCase {

	private const UNKNOWN_ID = 32202;
	private const KNOWN_ID = 31337;
	private const UPDATE_TOKEN = 'valid-update-token';

	/**
	 * @var DonationAuthorizer
	 */
	private $authorizer;

	/**
	 * @var DonationRepository
	 */
	private $repository;

	/**
	 * @var DonationConfirmationMailer|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $mailer;

	public function setUp() {
		$this->authorizer = new SucceedingDonationAuthorizer();
		$this->repository = new FakeDonationRepository( $this->newDonation() );
		$this->mailer = $this->createMock( DonationConfirmationMailer::class );
	}

	private function newDonation(): Donation {
		$donation = ValidDonation::newBankTransferDonation();
		$donation->assignId( self::KNOWN_ID );
		return $donation;
	}

	public function testWhenAuthorizationFails_errorIsReturned() {
		$this->authorizer = new FailingDonationAuthorizer();
		$eventHandler = $this->newDonationAcceptedEventHandler();

		$result = $eventHandler->onDonationAccepted( self::UNKNOWN_ID );

		$this->assertSame( DonationAcceptedEventHandler::AUTHORIZATION_FAILED, $result );
	}

	private function newDonationAcceptedEventHandler(): DonationAcceptedEventHandler {
		return new DonationAcceptedEventHandler(
			$this->authorizer,
			$this->repository,
			$this->mailer
		);
	}

	public function testGivenIdOfUnknownDonation_errorIsReturned() {
		$eventHandler = $this->newDonationAcceptedEventHandler();

		$result = $eventHandler->onDonationAccepted( self::UNKNOWN_ID );

		$this->assertSame( DonationAcceptedEventHandler::UNKNOWN_ID_PROVIDED, $result );
	}

	public function testGivenKnownIdAndValidAuth_successIsReturned() {
		$eventHandler = $this->newDonationAcceptedEventHandler();

		$result = $eventHandler->onDonationAccepted( self::KNOWN_ID );

		$this->assertSame( DonationAcceptedEventHandler::SUCCESS, $result );
	}

	public function testGivenKnownIdAndValidAuth_mailerIsInvoked() {
		$this->mailer->expects( $this->once() )
			->method( 'sendConfirmationMailFor' )
			->with( $this->equalTo( $this->newDonation() ) );

		$this->newDonationAcceptedEventHandler()->onDonationAccepted( self::KNOWN_ID );
	}

	public function testGivenIdOfUnknownDonation_mailerIsNotInvoked() {
		$this->mailer->expects( $this->never() )->method( $this->anything() );
		$this->newDonationAcceptedEventHandler()->onDonationAccepted( self::UNKNOWN_ID );
	}

	public function testWhenAuthorizationFails_mailerIsNotInvoked() {
		$this->authorizer = new FailingDonationAuthorizer();
		$this->mailer->expects( $this->never() )->method( $this->anything() );
		$this->newDonationAcceptedEventHandler()->onDonationAccepted( self::KNOWN_ID );
	}

}
