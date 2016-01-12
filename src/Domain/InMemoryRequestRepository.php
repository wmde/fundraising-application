<?php

namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Request;

/**
 * TODO Not sure if this is actually needed for unit tests if the DoctrineRequestRepository is used
 * with an in-memory sqlite database. Until we have a proper DI container, this class can't be initialized in
 * FunFunFactory
 *
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class InMemoryRequestRepository implements RequestRepository
{
	private $requests;

	/**
	 * @param Request[] $requests
	 */
	public function __construct( array $requests ) {
		$this->requests = $requests;
	}

	public function storeRequest( Request $request ) {
		$this->requests[] = $request;
	}

	public function getRequests(): array {
		return $this->requests;
	}

	public function setRequests( array $requests ) {
		$this->requests = $requests;
	}
}