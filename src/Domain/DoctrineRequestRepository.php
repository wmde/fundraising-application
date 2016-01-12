<?php


namespace WMDE\Fundraising\Frontend\Domain;

use Doctrine\DBAL\Connection;
use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Store\Factory as FundraisingStoreFactory;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class DoctrineRequestRepository implements RequestRepository
{
	private $entityManager;

	public function __construct( Connection $connection ) {
		$factory = new FundraisingStoreFactory( $connection );
		$this->entityManager = $factory->getEntityManager();
	}

	public function storeRequest( Request $request ) {
		$this->entityManager->persist( $request );
	}
}