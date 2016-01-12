<?php


namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Request;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface RequestRepository
{
	public function storeRequest( Request $request );
}