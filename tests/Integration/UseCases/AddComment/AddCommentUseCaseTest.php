<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddSubscription;

use WMDE\Fundraising\Frontend\Domain\Model\DonationComment;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\Tests\Fixtures\DonationRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ThrowingDonationRepository;
use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentUseCase
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddCommentUseCaseTest extends \PHPUnit_Framework_TestCase {

	const DONATION_ID = 9001;
	const COMMENT_TEXT = 'Your programmers deserve a raise';
	const COMMENT_IS_PUBLIC = true;
	const COMMENT_AUTHOR = 'Uncle Bob';

	public function testGivenValidRequest_commentGetsAdded() {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( self::DONATION_ID );

		$donationRepository = new FakeDonationRepository( $donation );

		$useCase = new AddCommentUseCase( $donationRepository, new SucceedingDonationAuthorizer() );
		$response = $useCase->addComment( $this->newValidRequest() );

		$this->assertEquals(
			new DonationComment(
				self::COMMENT_TEXT,
				self::COMMENT_IS_PUBLIC,
				self::COMMENT_AUTHOR
			),
			$donationRepository->getDonationById( self::DONATION_ID )->getComment()
		);

		$this->assertTrue( $response->isSuccessful() );
	}

	private function newValidRequest(): AddCommentRequest {
		$addCommentRequest = new AddCommentRequest();

		$addCommentRequest->setCommentText( self::COMMENT_TEXT );
		$addCommentRequest->setIsPublic( self::COMMENT_IS_PUBLIC );
		$addCommentRequest->setAuthorDisplayName( self::COMMENT_AUTHOR );
		$addCommentRequest->setDonationId( self::DONATION_ID );

		return $addCommentRequest->freeze()->assertNoNullFields();
	}

	public function testWhenRepositoryThrowsExceptionOnGet_failureResponseIsReturned() {
		$throwingRepo = new ThrowingDonationRepository();
		$throwingRepo->throwOnGetDonationById();

		$useCase = new AddCommentUseCase(
			$throwingRepo,
			new SucceedingDonationAuthorizer()
		);

		$response = $useCase->addComment( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	public function testWhenRepositoryThrowsExceptionOnStore_failureResponseIsReturned() {
		$throwingRepo = new ThrowingDonationRepository();
		$throwingRepo->throwOnStoreDonation();

		$useCase = new AddCommentUseCase(
			$throwingRepo,
			new SucceedingDonationAuthorizer()
		);

		$response = $useCase->addComment( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	public function testAuthorizationFails_failureResponseIsReturned() {
		$useCase = new AddCommentUseCase(
			new DonationRepositorySpy(),
			new FailingDonationAuthorizer()
		);

		$response = $useCase->addComment( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	public function testWhenDonationDoesNotExist_failureResponseIsReturned() {
		$useCase = new AddCommentUseCase( new FakeDonationRepository(), new SucceedingDonationAuthorizer() );
		$this->assertFalse( $useCase->addComment( $this->newValidRequest() )->isSuccessful() );
	}

}
