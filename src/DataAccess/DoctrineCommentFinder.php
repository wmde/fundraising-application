<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\CommentFinder;
use WMDE\Fundraising\Frontend\Domain\CommentListingException;
use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineCommentFinder implements CommentFinder {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @see CommentFinder::getPublicComments
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return CommentWithAmount[]
	 */
	public function getPublicComments( int $limit, int $offset = 0 ): array {
		$GLOBALS['profiler']->start( 'get_comments' );
		$comments = array_map(
			function( Donation $donation ) {
				return CommentWithAmount::newInstance()
					->setAuthorName( $donation->getPublicRecord() )
					->setCommentText( $donation->getComment() )
					->setDonationAmount( (float)$donation->getAmount() )
					->setDonationTime( $donation->getCreationTime() )
					->setDonationId( $donation->getId() )
					->freeze()
					->assertNoNullFields();
			},
			$this->getDonations( $limit, $offset )
		);
		$GLOBALS['profiler']->stop( 'get_comments' );

		return $comments;
	}

	private function getDonations( int $limit, int $offset ): array {
		try {
			$GLOBALS['profiler']->start( 'get_donations' );
			$donation = $this->entityManager->getRepository( Donation::class )->findBy(
				[
					'isPublic' => true,
					'deletionTime' => null
				],
				[
					'creationTime' => 'DESC'
				],
				$limit,
				$offset
			);
			$GLOBALS['profiler']->stop( 'get_donations' );
			return $donation;
		}
		catch ( ORMException $ex ) {
			throw new CommentListingException( $ex );
		}
	}

}
