<?php

namespace WMDE\Fundraising\Frontend\UseCases\DisplayPage;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageUseCase {

	public function getPage( PageDisplayRequest $listingRequest ): string {
		$pageName = $listingRequest->getPageName();

		return "<html><header />missing: $pageName</html>";
	}

}
