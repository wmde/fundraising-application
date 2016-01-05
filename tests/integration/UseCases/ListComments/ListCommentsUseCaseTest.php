<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Domain\InMemoryCommentRepository;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListItem;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListPresenter;
use WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsUseCaseTest extends \PHPUnit_Framework_TestCase {

	public function testWhenThereAreNoComments_anEmptyListIsPresented() {
		( new ListCommentsUseCase(
			$this->newPresenterThatExpects( new CommentList() ),
			new InMemoryCommentRepository( [] )
		) )->listComments( new CommentListingRequest( 10 ) );
	}

	/**
	 * @param CommentList $commentList
	 *
	 * @return CommentListPresenter
	 */
	private function newPresenterThatExpects( CommentList $commentList ) {
		$presenter = $this->getMock( 'WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListPresenter' );

		$presenter->expects( $this->once() )
			->method( 'listComments' )
			->with( $this->equalTo( $commentList ) );

		return $presenter;
	}

	public function testWhenThereAreLessCommentsThanTheLimit_theyAreAllPresented() {
		( new ListCommentsUseCase(
			$this->newPresenterThatExpects( new CommentList(
				new CommentListItem( 'name0', 'comment', '42', '000000' ),
				new CommentListItem( 'name1', 'comment', '42', '000000' ),
				new CommentListItem( 'name2', 'comment', '42', '000000' )
			) ),
			new InMemoryCommentRepository( [
				new Comment( 'name0', 'comment', '42', '000000' ),
				new Comment( 'name1', 'comment', '42', '000000' ),
				new Comment( 'name2', 'comment', '42', '000000' )
			] )
		) )->listComments( new CommentListingRequest( 10 ) );
	}

	public function testWhenThereAreMoreCommentsThanTheLimit_onlyTheFirstFewArePresented() {
		( new ListCommentsUseCase(
			$this->newPresenterThatExpects( new CommentList(
				new CommentListItem( 'name0', 'comment', '42', '000000' ),
				new CommentListItem( 'name1', 'comment', '42', '000000' )
			) ),
			new InMemoryCommentRepository( [
				new Comment( 'name0', 'comment', '42', '000000' ),
				new Comment( 'name1', 'comment', '42', '000000' ),
				new Comment( 'name2', 'comment', '42', '000000' ),
				new Comment( 'name3', 'comment', '42', '000000' ),
			] )
		) )->listComments( new CommentListingRequest( 2 ) );
	}

}
