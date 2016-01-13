<?php


namespace WMDE\Fundraising\Frontend\UseCases\AddSubscription;

use WMDE\Fundraising\Entities\Request;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddSubscriptionResponse {

	const TYPE_VALID = 'valid';
	const TYPE_INVALID = 'invalid';

	private $type;
	private $responseData = [];

	public static function createValidResponse( Request $request ) {
		$instance = new self();
		$instance->type = self::TYPE_VALID;
		$instance->responseData = [
			'request' => $request,
			'errors' => null
		];
		return $instance;
	}

	public static function createInvalidResponse( Request $request, array $errors ) {
		$instance = new self();
		$instance->type = self::TYPE_INVALID;
		$instance->responseData = [
			'request' => $request,
			'errors' => $errors
		];
		return $instance;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getResponseData():array {
		return $this->responseData;
	}

}