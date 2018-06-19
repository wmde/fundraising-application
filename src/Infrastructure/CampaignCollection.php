<?php
/**
 * Created by IntelliJ IDEA.
 * User: tozh
 * Date: 19.06.18
 * Time: 14:11
 */

namespace WMDE\Fundraising\Frontend\Infrastructure;

class CampaignCollection {
	private $campaigns;

	public function __construct( Campaign ...$campaigns ) {
		$this->campaigns = $campaigns;
	}

	public function splitGroupsFromCampaigns( array $params ): array {
		$groups = [];
		$campaigns = [];
		foreach ( $this->campaigns as $campaign ) {
			$urlKey = $campaign->getUrlKey();
			if ( isset( $params[ $urlKey ] ) && $group = $campaign->getGroupByIndex( $params[ $urlKey ]  ) ) {
				$groups []= $group;
				continue;
			}
			$campaigns []= $campaign;
		}
		return [ $groups, $campaigns ];
	}

	public function hasUrlParameters( string $urlKey, int $urlValue, $campaign ): bool {

	}

}