<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\PaymentContext\Domain;

/**
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class KontoCheckLibraryInitializationException extends \RuntimeException {

	/**
	 * @var string
	 */
	private $bankDataFile;

	public function __construct( string $bankDataFile, ?string $message = null, \Exception $previous = null ) {
		$this->bankDataFile = $bankDataFile;
		parent::__construct(
			$message !== null ?: 'Could not initialize library with bank data file: ' . $bankDataFile,
			0,
			$previous
		);
	}

	public function getBankDataFile(): string {
		return $this->bankDataFile;
	}

}
