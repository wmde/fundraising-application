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
	const CONFIG_KEY_LOGO = 'logo';
	const CONFIG_KEY_THEME = 'theme';
	const CONFIG_KEY_TESTMODE = 'testmode';

	private $baseUrl;
	private $projectId;
	private $backgroundColor;
	private $logo;
	private $theme;
	private $testMode;

	private function __construct( string $baseUrl, string $projectId, string $backgroundColor, string $logo, string $theme,
								  bool $testMode ) {
		$this->baseUrl = $baseUrl;
		$this->projectId = $projectId;
		$this->backgroundColor = $backgroundColor;
		$this->logo = $logo;
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
			$config[self::CONFIG_KEY_LOGO],
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

	public function getLogo(): string {
		return $this->logo;
	}

	public function getTheme(): string {
		return $this->theme;
	}

	public function isTestMode(): bool {
		return $this->testMode;
	}

}
