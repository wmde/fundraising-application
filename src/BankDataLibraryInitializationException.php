<?php

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
class BankDataLibraryInitializationException extends \RuntimeException {

	/**
	 * @var string
	 */
	private $bankDataFile;

	/**
	 * @param string $bankDataFile
	 * @param string|null $message
	 * @param \Exception $previous
	 */
	public function __construct( $bankDataFile, $message = null, \Exception $previous = null ) {
		$this->bankDataFile = $bankDataFile;
		parent::__construct(
			$message !== null ?: 'Could not initialize library with bank data file: ' . $bankDataFile,
			0,
			$previous
		);
	}

	/**
	 * @return string
	 */
	public function getBankDataFile() {
		return $this->bankDataFile;
	}

}
