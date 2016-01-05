<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\ListComments;

use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentListItem;
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
		$comment = new CommentListItem( 'name', 'comment', '42', '000000' );

		$this->assertSame( [$comment], ( new CommentList( $comment ) )->toArray() );
	}

	public function testGivenMultipleComments_constructorCreatesListWithAllComments() {
		$comment0 = new CommentListItem( 'name0', 'comment', '42', '000000' );
		$comment1 = new CommentListItem( 'name1', 'comment', '42', '000000' );
		$comment2 = new CommentListItem( 'name2', 'comment', '42', '000000' );

		$this->assertSame(
			[$comment0, $comment1, $comment2],
			( new CommentList( $comment0, $comment1, $comment2 ) )->toArray()
		);
	}

}
