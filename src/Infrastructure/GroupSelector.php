<?php

namespace WMDE\Fundraising\Frontend\Infrastructure;

class GroupSelector {

	private $campaigns;
 	private $cookie;
 	private $urlParameters;

	public function __construct( CampaignCollection $campaigns, array $cookie = [], array $urlParameters = [] ) {
		$this->campaigns = $campaigns;
		$this->cookie = $cookie;
		$this->urlParameters = $urlParameters;
	}

	public function setCookie( array $cookie ): self {
		$this->cookie = $this->sanitizeParameters( $cookie );
		return $this;
	}

	public function setUrlParameters( array $urlParameters ): self {
		$this->urlParameters = $this->sanitizeParameters( $urlParameters );
		return $this;
	}

	private function sanitizeParameters( array $params ): array {
		$sanitized = [];
		foreach ( $params as $key => $value ) {
			if ( ctype_digit( $value ) ) {
				$sanitized[$key] = intval( $value );
			}
		}
		return $sanitized;
	}

	/**
	 * @return Group[]
	 */
	public function selectGroups(): array {
		$possibleKeys = array_merge( $this->cookie, $this->urlParameters );
		[ $groups, $missingCampaigns ] = $this->campaigns->splitGroupsFromCampaigns( $possibleKeys );

		foreach($missingCampaigns as $campaign) {
				$groups []= $campaign->getGroups()[ array_rand( $campaign->getGroups() ) ];

		}
		return $groups;
	}

}