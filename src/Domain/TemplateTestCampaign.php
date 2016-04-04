<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TemplateTestCampaign {

	private $code;
	private $active;
	private $startTimestamp;
	private $endTimestamp;
	private $templates;

	public function __construct( array $campaignData ) {
		$this->code = $campaignData['code'];
		$this->active = $campaignData['active'];
		$this->startTimestamp = \DateTime::createFromFormat( 'Y-m-d H:i:s', $campaignData['startDate'] );
		$this->endTimestamp = \DateTime::createFromFormat( 'Y-m-d H:i:s', $campaignData['endDate'] );
		$this->templates = $campaignData['templates'];
	}

	public function getCode(): string {
		return $this->code;
	}

	public function isActive(): bool {
		return $this->active;
	}

	public function getStartTimestamp(): \DateTime {
		return $this->startTimestamp;
	}

	public function getEndTimestamp(): \DateTime {
		return $this->endTimestamp;
	}

	/**
	 * @return string[]
	 */
	public function getTemplates(): array {
		return $this->templates;
	}

	public function hasEnded(): bool {
		return $this->endTimestamp < new \DateTime();
	}

	public function hasStarted(): bool {
		return $this->startTimestamp < new \DateTime();
	}

	public function isRunning(): bool {
		return $this->isActive() && $this->hasStarted() && !$this->hasEnded();
	}

	public function getRandomTemplate() {
		return $this->templates[array_rand( $this->templates )];
	}

}
