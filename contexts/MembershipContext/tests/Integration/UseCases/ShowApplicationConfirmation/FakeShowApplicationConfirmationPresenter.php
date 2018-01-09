<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\Tests\Integration\UseCases\ShowApplicationConfirmation;

use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;
use WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation\ShowApplicationConfirmationPresenter;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeShowApplicationConfirmationPresenter implements ShowApplicationConfirmationPresenter {

	private $application;
	private $updateToken;
	private $anonymizedResponseWasShown = false;
	private $accessViolationWasShown = false;
	private $shownTechnicalError;

	public function presentConfirmation( Application $application, string $updateToken ): void {
		if ( $this->application !== null ) {
			throw new \RuntimeException( 'Presenter should only be invoked once' );
		}

		$this->application = $application;
		$this->updateToken = $updateToken;
	}

	public function getShownApplication(): Application {
		return $this->application;
	}

	public function getShownUpdateToken(): string {
		return $this->updateToken;
	}

	public function presentApplicationWasAnonymized(): void {
		$this->anonymizedResponseWasShown = true;
	}

	public function anonymizedResponseWasShown(): bool {
		return $this->anonymizedResponseWasShown;
	}

	public function presentAccessViolation(): void {
		$this->accessViolationWasShown = true;
	}

	public function accessViolationWasShown(): bool {
		return $this->accessViolationWasShown;
	}

	public function presentTechnicalError( string $error ): void {
		$this->shownTechnicalError = $error;
	}

	public function getShownTechnicalError(): string {
		return $this->shownTechnicalError;
	}

}