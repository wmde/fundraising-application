<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

use WMDE\Fundraising\Frontend\Presentation\Content\WikiContentProvider;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageUseCase {

	private $contentProvider;

	public function __construct( WikiContentProvider $contentProvider ) {
		$this->contentProvider = $contentProvider;
	}

	public function getPage( PageDisplayRequest $listingRequest ): PageDisplayResponse {
		$response = new PageDisplayResponse();

		$response->setMainContent( $this->contentProvider->getContent( $listingRequest->getPageName() ) );

		$response->freeze();
		$response->assertNoNullFields();

		return $response;
	}
}
