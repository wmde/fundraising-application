<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\ConfigValidation;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class ValidateConfigApplication extends Application {

	protected function getCommandName( InputInterface $input )
	{
		return ValidateConfigCommand::NAME;
	}

	protected function getDefaultCommands()
	{
		// Keep the core default commands to have the HelpCommand
		// which is used when using the --help option
		$defaultCommands = parent::getDefaultCommands();

		$defaultCommands[] = new ValidateConfigCommand();

		return $defaultCommands;
	}

	/**
	 * Overridden so that the application doesn't expect the command
	 * name to be the first argument.
	 */
	public function getDefinition()
	{
		$inputDefinition = parent::getDefinition();
		// clear out the normal first argument, which is the command name
		$inputDefinition->setArguments();

		return $inputDefinition;
	}

}