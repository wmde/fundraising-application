<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Integration\UseCases\ListComments;

use WMDE\Fundraising\Frontend\DonationContext\Domain\Repositories\CommentWithAmount;
use WMDE\Fundraising\Frontend\DonationContext\Tests\Fixtures\InMemoryCommentFinder;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments\CommentList;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments\ListCommentsUseCase;

/**
 * @covers WMDE\Fundraising\Frontend\DonationContext\UseCases\ListComments\ListCommentsUseCase
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ListCommentsUseCaseTest extends \PHPUnit\Framework\TestCase {

	public function testWhenThereAreNoComments_anEmptyListIsPresented(): void {
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder() );

		$this->assertEquals(
			new CommentList(),
			$useCase->listComments( new CommentListingRequest( 10, CommentListingRequest::FIRST_PAGE ) )
		);
	}

	public function testWhenThereAreLessCommentsThanTheLimit_theyAreAllPresented(): void {
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder(
			$this->newCommentWithAuthorName( 'name0' ),
			$this->newCommentWithAuthorName( 'name1' ),
			$this->newCommentWithAuthorName( 'name2' )
		) );

		$this->assertEquals(
			new CommentList(
				$this->newCommentWithAuthorName( 'name0' ),
				$this->newCommentWithAuthorName( 'name1' ),
				$this->newCommentWithAuthorName( 'name2' )
			),
			$useCase->listComments( new CommentListingRequest( 10, CommentListingRequest::FIRST_PAGE ) )
		);
	}

	private function newCommentWithAuthorName( string $authorName ): CommentWithAmount {
		return CommentWithAmount::newInstance()
			->setAuthorName( $authorName )
			->setCommentText( 'comment' )
			->setDonationAmount( 42 )
			->setDonationTime( new \DateTime( '1984-01-01' ) );
	}

	public function testWhenThereAreMoreCommentsThanTheLimit_onlyTheFirstFewArePresented(): void {
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder(
			$this->newCommentWithAuthorName( 'name0' ),
			$this->newCommentWithAuthorName( 'name1' ),
			$this->newCommentWithAuthorName( 'name2' ),
			$this->newCommentWithAuthorName( 'name3' )
		) );

		$this->assertEquals(
			new CommentList(
				$this->newCommentWithAuthorName( 'name0' ),
				$this->newCommentWithAuthorName( 'name1' )
			),
			$useCase->listComments( new CommentListingRequest( 2, CommentListingRequest::FIRST_PAGE ) )
		);
	}

	public function testWhenPageParameterIsTwo_correctOffsetIsUsed(): void {
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder(
			$this->newCommentWithAuthorName( 'name0' ),
			$this->newCommentWithAuthorName( 'name1' ),
			$this->newCommentWithAuthorName( 'name2' ),
			$this->newCommentWithAuthorName( 'name3' )
		) );

		$this->assertEquals(
			new CommentList(
				$this->newCommentWithAuthorName( 'name3' )
			),
			$useCase->listComments( new CommentListingRequest( 3, 2 ) )
		);
	}

	/**
	 * @dataProvider invalidPageNumberProvider
	 */
	public function testGivenInvalidPageNumber_firstPageIsReturned( int $invalidPageNumber ): void {
		$useCase = new ListCommentsUseCase( new InMemoryCommentFinder(
			$this->newCommentWithAuthorName( 'name0' ),
			$this->newCommentWithAuthorName( 'name1' ),
			$this->newCommentWithAuthorName( 'name2' ),
			$this->newCommentWithAuthorName( 'name3' )
		) );

		$this->assertEquals(
			new CommentList(
				$this->newCommentWithAuthorName( 'name0' ),
				$this->newCommentWithAuthorName( 'name1' )
			),
			$useCase->listComments( new CommentListingRequest( 2, $invalidPageNumber ) )
		);
	}

	public function invalidPageNumberProvider(): array {
		return [
			'too big' => [ 31337 ],
			'upper limit boundary' => [ 101 ],
			'lower limit boundary' => [ 0 ],
			'too small' => [ -10 ],
		];
	}

	/**
	 * @dataProvider invalidLimitProvider
	 */
	public function testGivenInvalidLimit_10resultsAreReturned( int $invalidLimit ): void {
		$useCase = new ListCommentsUseCase( $this->newInMemoryCommentFinderWithComments( 20 ) );

		$commentList = $useCase->listComments( new CommentListingRequest(
			$invalidLimit,
			CommentListingRequest::FIRST_PAGE
		) );

		$this->assertCount( 10, $commentList->toArray() );
	}

	private function newInMemoryCommentFinderWithComments( int $commentCount ): InMemoryCommentFinder {
		return new InMemoryCommentFinder(
			...new \LimitIterator(
				$this->newInfiniteCommentIterator(),
				0,
				$commentCount
			)
		);
	}

	private function newInfiniteCommentIterator(): \Iterator {
		$commentNumber = 0;

		while ( true ) {
			yield $this->newCommentWithAuthorName( 'name' . $commentNumber++ );
		}
	}

	public function invalidLimitProvider(): array {
		return [
			'too big' => [ 31337 ],
			'upper limit boundary' => [ 101 ],
			'lower limit boundary' => [ 0 ],
			'too small' => [ -10 ],
		];
	}

}
