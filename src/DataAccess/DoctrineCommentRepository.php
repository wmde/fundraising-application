<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\Comment;
use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentFinder;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreCommentException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DoctrineCommentRepository implements CommentRepository, CommentFinder {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @see CommentRepository::getPublicComments
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
			$this->getDonation( $limit )
		);
	}

	private function getDonation( int $limit ): array {
		// FIXME: catch exception
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

	/**
	 * @param Comment $comment
	 *
	 * @throws StoreCommentException
	 */
	public function storeComment( Comment $comment ) {
		try {
			/**
			 * @var Donation $donation
			 */
			$donation = $this->entityManager->find( Donation::class, $comment->getDonationId() );

			if ( !is_object( $donation ) ) {
				throw new StoreCommentException();
			}

			$donation->setIsPublic( $comment->isPublic() );
			$donation->setComment( $comment->getCommentText() );
			$donation->setPublicRecord( $comment->getAuthorDisplayName() );

			$this->entityManager->persist( $donation );
			$this->entityManager->flush();
		}
		catch ( ORMException $ex ) {
			throw new StoreCommentException();
		}
	}

}
