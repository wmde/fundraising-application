<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityRepository;
use WMDE\Fundraising\Entities\Spenden;
use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DbalCommentRepository implements CommentRepository {

	private $entityRepository;

	public function __construct( EntityRepository $entityRepository ) {
		$this->entityRepository = $entityRepository;
	}

	/**
	 * Returns the comments that can be shown to non-privileged users.
	 *
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getPublicComments( int $limit ): array {
		return array_map(
			function( Spenden $spenden ) {
				return Comment::newInstance()
					->setAuthorName( $spenden->getName() )
					->setCommentText( $spenden->getKommentar() )
					->setDonationAmount( (float)$spenden->getBetrag() )
					->setPostingTime( $spenden->getDtNew() )
					->setDonationId( $spenden->getId() )
					->freeze()
					->assertNoNullFields();
			},
			$this->getSpenden( $limit )
		);
	}

	private function getSpenden( int $limit ): array {
		return $this->entityRepository->findBy(
			[
				'isPublic' => true,
				'dtDel' => null
			],
			null,
			$limit
		);
	}

}
