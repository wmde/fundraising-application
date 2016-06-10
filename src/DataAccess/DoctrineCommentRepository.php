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
class DoctrineCommentRepository implements CommentFinder {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @see CommentFinder::getPublicComments
	 *
	 * @param int $limit
	 *
	 * @return CommentWithAmount[]
	 */
	public function getPublicComments( int $limit ): array {
		return array_map(
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
			$this->getDonations( $limit )
		);
	}

	private function getDonations( int $limit ): array {
		try {
			return $this->entityManager->getRepository( Donation::class )->findBy(
				[
					'isPublic' => true,
					'deletionTime' => null
				],
				[
					'creationTime' => 'DESC'
				],
				$limit
			);
		}
		catch ( ORMException $ex ) {
			throw new CommentListingException( $ex );
		}
	}

}
