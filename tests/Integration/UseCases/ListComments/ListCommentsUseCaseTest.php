<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Domain\InMemoryCommentRepository;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testWhenThereAreNoComments_anEmptyListIsPresented() {
		$useCase = new ListCommentsUseCase( new InMemoryCommentRepository() );

		$this->assertEquals(
			new CommentList(),
			$useCase->listComments( new CommentListingRequest( 10 ) )
		);
	}

	public function testWhenThereAreLessCommentsThanTheLimit_theyAreAllPresented() {
		$useCase = new ListCommentsUseCase( new InMemoryCommentRepository(
			Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
			Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
			Comment::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) )
		) );

		$this->assertEquals(
			new CommentList(
				Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) )
			),
			$useCase->listComments( new CommentListingRequest( 10 ) )
		);
	}

	public function testWhenThereAreMoreCommentsThanTheLimit_onlyTheFirstFewArePresented() {
		$useCase = new ListCommentsUseCase( new InMemoryCommentRepository(
			Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
			Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
			Comment::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
			Comment::newInstance()->setAuthorName( 'name3' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) )
		) );

		$this->assertEquals(
			new CommentList(
				Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) )
			),
			$useCase->listComments( new CommentListingRequest( 2 ) )
		);
	}

}
