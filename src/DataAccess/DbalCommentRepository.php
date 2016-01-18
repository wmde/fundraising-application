<?php

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
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getComments( int $limit ): array {
		return array_map(
			function( Spenden $spenden ) {
				return Comment::newInstance()
					->setAuthorName( $spenden->getName() )
					->setCommentText( $spenden->getKommentar() )
					->setDonationAmount( $spenden->getBetrag() )
					->setPostingTime( $spenden->getDtNew() );
			},
			$this->entityRepository->findBy( [], null, $limit )
		);
	}

}
