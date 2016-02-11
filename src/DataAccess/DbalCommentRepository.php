<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityRepository;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\ReadModel\CommentWithAmount;
use WMDE\Fundraising\Frontend\Domain\Repositories\CommentFinder;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DbalCommentRepository implements CommentFinder {

	private $entityRepository;

	public function __construct( EntityRepository $entityRepository ) {
		$this->entityRepository = $entityRepository;
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
					->setDonationTime( $donation->getDtNew() )
					->setDonationId( $donation->getId() )
					->freeze()
					->assertNoNullFields();
			},
			$this->getDonation( $limit )
		);
	}

	private function getDonation( int $limit ): array {
		return $this->entityRepository->findBy(
			[
				'isPublic' => true,
				'dtDel' => null
			],
			[
				'dtNew' => 'DESC'
			],
			$limit
		);
	}

}
