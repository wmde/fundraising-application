<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli;

use Symfony\Component\Console\Application;
use WMDE\Fundraising\Frontend\Cli\ApplicationConfigValidation\ValidateApplicationConfigCommand;
use WMDE\Fundraising\Frontend\Cli\CampaignConfigValidation\ValidateCampaignConfigCommand;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class FundraisingCli {

	/**
	 * @var Application
	 */
	private $app;

	public function newApplication(): Application {
		$this->app = new Application();
		$this->setApplicationInfo();
		$this->registerCommands();
		return $this->app;
	}
	private function setApplicationInfo(): void {
		$this->app->setName( 'Fundraising Console' );
		$this->app->setVersion( '2.0' );
	}
	private function registerCommands(): void {
		$this->app->add( new ValidateApplicationConfigCommand() );
		$this->app->add( new ValidateCampaignConfigCommand() );
		$this->app->add( new RenderMailTemplatesCommand() );
	}
	public function run(): void {
		$this->newApplication()->run();
	}

}