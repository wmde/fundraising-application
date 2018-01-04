<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\MembershipContext\UseCases\ShowApplicationConfirmation;

use WMDE\Fundraising\Frontend\MembershipContext\Domain\Model\Application;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface ShowApplicationConfirmationPresenter {

	public function presentConfirmation( Application $application, string $updateToken ): void;

	public function presentApplicationWasPurged(): void;

	public function presentAccessViolation(): void;

	public function presentTechnicalError( string $message ): void;

}