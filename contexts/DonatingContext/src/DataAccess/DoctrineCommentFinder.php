<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonatingContext\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentListingException;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\CommentWithAmount;

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
			$this->getDonations( $limit, $offset )
		);
	}

	private function getDonations( int $limit, int $offset ): array {
		try {
			return $this->entityManager->getRepository( Donation::class )->findBy(
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
		}
		catch ( ORMException $ex ) {
			throw new CommentListingException( $ex );
		}
	}

}
