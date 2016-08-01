<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\ApplicationContext\UseCases\DisplayPage;

use WMDE\Fundraising\Frontend\Validation\TemplateNameValidator;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageUseCase {

	private $templateNameValidator;

	public function __construct( TemplateNameValidator $templateNameValidator ) {
		$this->templateNameValidator = $templateNameValidator;
	}

	public function getPage( PageDisplayRequest $listingRequest ): PageDisplayResponse {
		$response = new PageDisplayResponse();
		$response->setMainContentTemplate( $listingRequest->getPageName() );
		$templateExists = $this->templateNameValidator->validate( $listingRequest->getPageName() )->isSuccessful();
		$response->setTemplateExists( $templateExists );
		$response->freeze();
		$response->assertNoNullFields();

		return $response;
	}
}
