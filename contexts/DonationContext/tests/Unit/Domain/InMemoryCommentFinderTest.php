<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Unit\Domain;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\CommentWithAmount;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\InMemoryCommentFinder;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\InMemoryCommentFinder
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryCommentFinderTest extends \PHPUnit\Framework\TestCase {

	public function testWhenThereAreNoComments_getCommentsReturnsEmptyArray(): void {
		$this->assertSame( [], ( new InMemoryCommentFinder() )->getPublicComments( 10 ) );
	}

	public function testWhenThereAreComments_getCommentsReturnsThem(): void {
		$this->assertEquals(
			[
				CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
			],
			( new InMemoryCommentFinder(
				CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) )
			) )->getPublicComments( 10 )
		);
	}

	public function testGivenLimitSmallerThanCommentCount_getCommentsLimitsItsResult(): void {
		$this->assertEquals(
			[
				CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) )
			],
			( new InMemoryCommentFinder(
				CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( 42 )->setDonationTime( new \DateTime( '1984-01-01' ) )
			) )->getPublicComments( 2 )
		);
	}

}
