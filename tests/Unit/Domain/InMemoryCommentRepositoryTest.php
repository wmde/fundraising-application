<?php

declare(strict_types=1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Domain\InMemoryCommentRepository;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\InMemoryCommentRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryCommentRepositoryTest extends \PHPUnit_Framework_TestCase {

	public function testWhenThereAreNoComments_getCommentsReturnsEmptyArray() {
		$this->assertSame( [], ( new InMemoryCommentRepository() )->getComments( 10 ) );
	}

	public function testWhenThereAreComments_getCommentsReturnsThem() {
		$this->assertEquals(
			[
				new Comment( 'name0', 'comment', 42, 0 ),
				new Comment( 'name1', 'comment', 42, 0 ),
				new Comment( 'name2', 'comment', 42, 0 )
			],
			( new InMemoryCommentRepository(
				new Comment( 'name0', 'comment', 42, 0 ),
				new Comment( 'name1', 'comment', 42, 0 ),
				new Comment( 'name2', 'comment', 42, 0 )
			) )->getComments( 10 )
		);
	}

	public function testGivenLimitSmallerThanCommentCount_getCommentsLimitsItsResult() {
		$this->assertEquals(
			[
				new Comment( 'name0', 'comment', 42, 0 ),
				new Comment( 'name1', 'comment', 42, 0 ),
			],
			( new InMemoryCommentRepository(
				new Comment( 'name0', 'comment', 42, 0 ),
				new Comment( 'name1', 'comment', 42, 0 ),
				new Comment( 'name2', 'comment', 42, 0 )
			) )->getComments( 2 )
		);
	}

}
