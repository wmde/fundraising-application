<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BannerImpressionData\DataAccess;

use WMDE\Fundraising\Frontend\BannerImpressionData\Domain\BannerImpression;

class BannerImpressionDataRepository {

	public function storeImpression( BannerImpression $impressionData ): void {

	}

	public function getLastStoredImpressionDate(): \DateTimeImmutable {
		throw new \Exception( "Not implemented" );
	}
}
