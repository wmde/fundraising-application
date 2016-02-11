<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;
use WMDE\Fundraising\Frontend\Tests\Fixtures\InMemoryCommentFinder;
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
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder() );

		$this->assertEquals(
			new CommentList(),
			$useCase->listComments( new CommentListingRequest( 10 ) )
		);
	}

	public function testWhenThereAreLessCommentsThanTheLimit_theyAreAllPresented() {
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder(
			CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
			CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
			CommentWithAmount::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) )
		) );

		$this->assertEquals(
			new CommentList(
				CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) )
			),
			$useCase->listComments( new CommentListingRequest( 10 ) )
		);
	}

	public function testWhenThereAreMoreCommentsThanTheLimit_onlyTheFirstFewArePresented() {
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder(
			CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
			CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
			CommentWithAmount::newInstance()->setAuthorName( 'name2' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
			CommentWithAmount::newInstance()->setAuthorName( 'name3' )->setCommentText( 'comment' )
				->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) )
		) );

		$this->assertEquals(
			new CommentList(
				CommentWithAmount::newInstance()->setAuthorName( 'name0' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) ),
				CommentWithAmount::newInstance()->setAuthorName( 'name1' )->setCommentText( 'comment' )
					->setDonationAmount( '42' )->setDonationTime( new \DateTime( '1984-01-01' ) )
			),
			$useCase->listComments( new CommentListingRequest( 2 ) )
		);
	}

}
