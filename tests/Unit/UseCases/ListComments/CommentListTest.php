<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentList;

/**
 * @covers WMDE\Fundraising\Frontend\UseCases\ListComments\CommentList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNoArguments_constructorCreatesEmptyList() {
		$this->assertSame( [], ( new CommentList() )->toArray() );
	}

	public function testGivenOneComment_constructorCreatesListWithComment() {
		$comment = Comment::newInstance()
			->setAuthorName( 'name0' )
			->setCommentText( 'comment' )
			->setDonationAmount( '42' )
			->setPostingTime( new \DateTime( '1984-01-01' ) );

		$this->assertSame( [ $comment ], ( new CommentList( $comment ) )->toArray() );
	}

	public function testGivenMultipleComments_constructorCreatesListWithAllComments() {
		$comment0 = Comment::newInstance()
			->setAuthorName( 'name0' )
			->setCommentText( 'comment' )
			->setDonationAmount( '42' )
			->setPostingTime( new \DateTime( '1984-01-01' ) );

		$comment1 = Comment::newInstance()
			->setAuthorName( 'name1' )
			->setCommentText( 'comment' )
			->setDonationAmount( '42' )
			->setPostingTime( new \DateTime( '1984-01-01' ) );

		$comment2 = Comment::newInstance()
			->setAuthorName( 'name2' )
			->setCommentText( 'comment' )
			->setDonationAmount( '42' )
			->setPostingTime( new \DateTime( '1984-01-01' ) );

		$this->assertSame(
			[ $comment0, $comment1, $comment2 ],
			( new CommentList( $comment0, $comment1, $comment2 ) )->toArray()
		);
	}

}
