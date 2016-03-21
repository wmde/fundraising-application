<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\AddSubscription;

use WMDE\Fundraising\Frontend\Domain\Model\Comment;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreCommentException;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CommentRepositorySpy;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FailingDonationAuthorizer;
use WMDE\Fundraising\Frontend\Tests\Fixtures\SucceedingDonationAuthorizer;
use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentUseCase
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddCommentUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testGivenValidRequest_commentGetsAdded() {
		$addCommentRequest = new AddCommentRequest();
		$addCommentRequest->setCommentText( 'Your programmers deserve a raise' );
		$addCommentRequest->setIsPublic( true );
		$addCommentRequest->setAuthorDisplayName( 'Uncle Bob' );
		$addCommentRequest->setDonationId( 9001 );
		$addCommentRequest->freeze()->assertNoNullFields();

		$commentRepository = new CommentRepositorySpy();
		$useCase = new AddCommentUseCase( $commentRepository, new SucceedingDonationAuthorizer() );
		$response = $useCase->addComment( $addCommentRequest );

		$expectedComment = new Comment();
		$expectedComment->setAuthorDisplayName( 'Uncle Bob' );
		$expectedComment->setDonationId( 9001 );
		$expectedComment->setCommentText( 'Your programmers deserve a raise' );
		$expectedComment->setIsPublic( true );
		$expectedComment->freeze()->assertNoNullFields();

		$this->assertTrue( $response->isSuccessful() );
		$this->assertEquals(
			[ $expectedComment ],
			$commentRepository->getStoreCommentCalls()
		);
	}

	public function testWhenRepositoryThrowsException_addCommentReturnsFailureResponse() {
		$addCommentRequest = new AddCommentRequest();
		$addCommentRequest->setCommentText( 'Your programmers deserve a raise' );
		$addCommentRequest->setIsPublic( true );
		$addCommentRequest->setAuthorDisplayName( 'Uncle Bob' );
		$addCommentRequest->setDonationId( 9001 );
		$addCommentRequest->freeze()->assertNoNullFields();

		$useCase = new AddCommentUseCase(
			$this->newThrowingCommentRepository(),
			new SucceedingDonationAuthorizer()
		);

		$response = $useCase->addComment( $addCommentRequest );

		$this->assertFalse( $response->isSuccessful() );
	}

	private function newThrowingCommentRepository(): CommentRepository {
		return new class () implements CommentRepository {
			public function storeComment( Comment $comment ) {
				throw new StoreCommentException();
			}
		};
	}

	public function testAuthorizationFails_failureResponseIsReturned() {
		$addCommentRequest = new AddCommentRequest();
		$addCommentRequest->setCommentText( 'Your programmers deserve a raise' );
		$addCommentRequest->setIsPublic( true );
		$addCommentRequest->setAuthorDisplayName( 'Uncle Bob' );
		$addCommentRequest->setDonationId( 9001 );
		$addCommentRequest->freeze()->assertNoNullFields();

		$useCase = new AddCommentUseCase(
			new CommentRepositorySpy(),
			new FailingDonationAuthorizer()
		);

		$response = $useCase->addComment( $addCommentRequest );

		$this->assertFalse( $response->isSuccessful() );
	}

}
