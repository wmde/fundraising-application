<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Entities\Request;
use WMDE\Fundraising\Frontend\Domain\RequestRepository;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class RequestRepositorySpy implements RequestRepository {

	private $requests = [];

	public function storeRequest( Request $request ) {
		$this->requests[] = $request;
	}

	/**
	 * @return Request[]
	 */
	public function getRequests(): array {
		return $this->requests;
	}

}