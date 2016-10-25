<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * TODO: move to Infrastructure
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTrackingInfo {
	use FreezableValueObject;

	private $tracking;
	private $source;
	private $totalImpressionCount;
	private $singleBannerImpressionCount;
	private $color;
	private $skin;
	private $layout;

	public function getTracking(): string {
		return $this->tracking;
	}

	public function setTracking( string $tracking ) {
		$this->assertIsWritable();
		$this->tracking = $tracking;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function setSource( string $source ) {
		$this->assertIsWritable();
		$this->source = $source;
	}

	public function getTotalImpressionCount(): int {
		return $this->totalImpressionCount;
	}

	public function setTotalImpressionCount( int $totalImpressionCount ) {
		$this->assertIsWritable();
		$this->totalImpressionCount = $totalImpressionCount;
	}

	public function getSingleBannerImpressionCount(): int {
		return $this->singleBannerImpressionCount;
	}

	public function setSingleBannerImpressionCount( int $singleBannerImpressionCount ) {
		$this->assertIsWritable();
		$this->singleBannerImpressionCount = $singleBannerImpressionCount;
	}

	public function getColor(): string {
		return $this->color;
	}

	public function setColor( string $color ) {
		$this->assertIsWritable();
		$this->color = $color;
	}

	public function getSkin(): string {
		return $this->skin;
	}

	public function setSkin( string $skin ) {
		$this->assertIsWritable();
		$this->skin = $skin;
	}

	public function getLayout(): string {
		return $this->layout;
	}

	public function setLayout( string $layout ) {
		$this->assertIsWritable();
		$this->layout = $layout;
	}

	public static function newBlankTrackingInfo(): self {
		$trackingInfo = new self();
		$trackingInfo->setColor( '' );
		$trackingInfo->setLayout( '' );
		$trackingInfo->setSingleBannerImpressionCount( 0 );
		$trackingInfo->setSkin( '' );
		$trackingInfo->setSource( '' );
		$trackingInfo->setTotalImpressionCount( 0 );
		$trackingInfo->setTracking( '' );
		return $trackingInfo;
	}

}
