<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Integration\UseCases\ShowApplicationConfirmation;

use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationResponse;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeShowApplicationConfirmationPresenter implements ShowApplicationConfirmationPresenter {

	private $responseModel;
	private $purgedResponseWasShown = false;

	public function presentResponseModel( ShowApplicationConfirmationResponse $response ): void {
		if ( $this->responseModel !== null ) {
			throw new \RuntimeException( 'Presenter should only be invoked once' );
		}

		$this->responseModel = $response;
	}

	public function getResponseModel(): ?ShowApplicationConfirmationResponse {
		return $this->responseModel;
	}

	public function presentApplicationWasPurged(): void {
		$this->purgedResponseWasShown = true;
	}

	public function purgedResponseWasShown(): bool {
		return $this->purgedResponseWasShown;
	}

}