<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\DonationContext\UseCases\AddComment;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonationComment;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddComment\AddCommentUseCase;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddComment\AddCommentValidationResult;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\AddComment\AddCommentValidator;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\FakeDonationRepository;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\ThrowingDonationRepository;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\UseCases\AddComment\AddCommentUseCase
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddCommentUseCaseTest extends \PHPUnit_Framework_TestCase {

	const DONATION_ID = 9001;
	const COMMENT_TEXT = 'Your programmers deserve a raise';
	const COMMENT_IS_PUBLIC = true;
	const COMMENT_AUTHOR = 'Uncle Bob';

	private $donationRepository;
	private $authorizer;
	private $textPolicyValidator;
	private $commentValidator;

	public function setUp() {
		$this->donationRepository = new FakeDonationRepository();
		$this->authorizer = new SucceedingDonationAuthorizer();
		$this->textPolicyValidator = $this->newSucceedingTextPolicyValidator();
		$this->commentValidator = $this->newSucceedingAddCommentValidator();
	}

	private function newSucceedingTextPolicyValidator(): TextPolicyValidator {
		return $this->newStubTextPolicyValidator( true );
	}

	private function newStubTextPolicyValidator( bool $returnValue ): TextPolicyValidator {
		$validator = $this->createMock( TextPolicyValidator::class );

		$validator->expects( $this->any() )->method( 'textIsHarmless' )->willReturn( $returnValue );

		return $validator;
	}

	public function testGivenValidRequest_commentGetsAdded() {
		$this->donationRepository = $this->newFakeRepositoryWithDonation();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );

		$this->assertEquals(
			new DonationComment(
				self::COMMENT_TEXT,
				self::COMMENT_IS_PUBLIC,
				self::COMMENT_AUTHOR
			),
			$this->donationRepository->getDonationById( self::DONATION_ID )->getComment()
		);

		$this->assertTrue( $response->isSuccessful() );
	}

	private function newFakeRepositoryWithDonation(): FakeDonationRepository {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( self::DONATION_ID );

		return new FakeDonationRepository( $donation );
	}

	private function newUseCase(): AddCommentUseCase {
		return new AddCommentUseCase(
			$this->donationRepository,
			$this->authorizer,
			$this->textPolicyValidator,
			$this->commentValidator
		);
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
		$this->donationRepository = new ThrowingDonationRepository();
		$this->donationRepository->throwOnGetDonationById();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	public function testWhenRepositoryThrowsExceptionOnStore_failureResponseIsReturned() {
		$this->donationRepository = new ThrowingDonationRepository();
		$this->donationRepository->throwOnStoreDonation();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	public function testAuthorizationFails_failureResponseIsReturned() {
		$this->authorizer = new FailingDonationAuthorizer();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );

		$this->assertFalse( $response->isSuccessful() );
	}

	public function testWhenDonationDoesNotExist_failureResponseIsReturned() {
		$this->assertFalse( $this->newUseCase()->addComment( $this->newValidRequest() )->isSuccessful() );
	}

	public function testWhenTextValidationFails_commentIsMadePrivate() {
		$this->donationRepository = $this->newFakeRepositoryWithDonation();
		$this->textPolicyValidator = $this->newFailingTextPolicyValidator();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );
		$this->assertTrue( $response->isSuccessful() );

		$this->assertFalse(
			$this->donationRepository->getDonationById( self::DONATION_ID )->getComment()->isPublic()
		);
	}

	private function newFailingTextPolicyValidator(): TextPolicyValidator {
		return $this->newStubTextPolicyValidator( false );
	}

	public function testWhenTextValidationFails_donationIsMarkedForModeration() {
		$this->donationRepository = $this->newFakeRepositoryWithDonation();
		$this->textPolicyValidator = $this->newFailingTextPolicyValidator();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );
		$this->assertTrue( $response->isSuccessful() );

		$this->assertTrue(
			$this->donationRepository->getDonationById( self::DONATION_ID )->needsModeration()
		);
	}

	public function testWhenTextValidationFails_responseMessageDoesNotContainOK() {
		$this->donationRepository = $this->newFakeRepositoryWithDonation();
		$this->textPolicyValidator = $this->newFailingTextPolicyValidator();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );
		$this->assertTrue( $response->isSuccessful() );

		$this->assertNotContains( 'ok', $response->getSuccessMessage() );
	}

	public function testWhenDonationIsMarkedForModeration_responseMessageDoesNotContainOK() {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( self::DONATION_ID );
		$donation->markForModeration();

		$this->donationRepository = new FakeDonationRepository( $donation );
		$this->textPolicyValidator = $this->newFailingTextPolicyValidator();

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );
		$this->assertTrue( $response->isSuccessful() );

		$this->assertNotContains( 'ok', $response->getSuccessMessage() );
	}

	public function testWhenValidationFails_failureResponseIsReturned() {
		$this->donationRepository = $this->newFakeRepositoryWithDonation();
		$this->commentValidator = $this->createMock( AddCommentValidator::class );
		$this->commentValidator->method( 'validate' )->willReturn( new AddCommentValidationResult( [
			'comment' => 'failed'
		] ) );

		$response = $this->newUseCase()->addComment( $this->newValidRequest() );
		$this->assertFalse( $response->isSuccessful() );
	}

	private function newSucceedingAddCommentValidator() {
		$validator = $this->createMock( AddCommentValidator::class );
		$validator->method( 'validate' )->willReturn( new AddCommentValidationResult( [] ) );
		return $validator;
	}

}
