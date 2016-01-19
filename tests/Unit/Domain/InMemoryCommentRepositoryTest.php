<?php

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
				Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
			],
			( new InMemoryCommentRepository(
				Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) )
			) )->getComments( 10 )
		);
	}

	public function testGivenLimitSmallerThanCommentCount_getCommentsLimitsItsResult() {
		$this->assertEquals(
			[
				Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) )
			],
			( new InMemoryCommentRepository(
				Comment::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setPostingTime( new \DateTime( '1984-01-01' ) )
			) )->getComments( 2 )
		);
	}

}
