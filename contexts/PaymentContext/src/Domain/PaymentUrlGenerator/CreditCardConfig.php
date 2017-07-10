<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\PaymentContext\Domain\PaymentUrlGenerator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CreditCardConfig {

	const CONFIG_KEY_BASE_URL = 'base-url';
	const CONFIG_KEY_PROJECT_ID = 'project-id';
	const CONFIG_KEY_BACKGROUND_COLOR = 'background-color';
	const CONFIG_KEY_SKIN = 'skin';
	const CONFIG_KEY_THEME = 'theme';
	const CONFIG_KEY_TESTMODE = 'testmode';

	private $baseUrl;
	private $projectId;
	private $backgroundColor;
	private $skin;
	private $theme;
	private $testMode;

	private function __construct( string $baseUrl, string $projectId, string $backgroundColor, string $skin, string $theme,
								  bool $testMode ) {
		$this->baseUrl = $baseUrl;
		$this->projectId = $projectId;
		$this->backgroundColor = $backgroundColor;
		$this->skin = $skin;
		$this->theme = $theme;
		$this->testMode = $testMode;
	}

	/**
	 * @param array $config
	 * @return self
	 * @throws \RuntimeException
	 */
	public static function newFromConfig( array $config ): self {
		return ( new self(
			$config[self::CONFIG_KEY_BASE_URL],
			$config[self::CONFIG_KEY_PROJECT_ID],
			$config[self::CONFIG_KEY_BACKGROUND_COLOR],
			$config[self::CONFIG_KEY_SKIN],
			$config[self::CONFIG_KEY_THEME],
			$config[self::CONFIG_KEY_TESTMODE]
		) )->assertNoEmptyFields();
	}

	private function assertNoEmptyFields(): self {
		foreach ( get_object_vars( $this ) as $fieldName => $fieldValue ) {
			if ( !isset( $fieldValue ) || $fieldValue === '' ) {
				throw new \RuntimeException( "Configuration variable '$fieldName' can not be empty" );
			}
		}

		return $this;
	}

	public function getBaseUrl(): string {
		return $this->baseUrl;
	}

	public function getProjectId(): string {
		return $this->projectId;
	}

	public function getBackgroundColor(): string {
		return $this->backgroundColor;
	}

	public function getSkin(): string {
		return $this->skin;
	}

	public function getTheme(): string {
		return $this->theme;
	}

	public function isTestMode() {
		return $this->testMode;
	}

}
