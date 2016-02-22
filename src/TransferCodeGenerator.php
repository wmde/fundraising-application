<?php

namespace WMDE\Fundraising\Frontend;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TransferCodeGenerator {

	public function generateTransferCode() {
		$transferCode = 'W-Q-';

		for ( $i = 0; $i < 6; ++$i ) {
			$transferCode .= $this->getRandomCharacter();
		}
		$transferCode .= '-' . $this->getRandomCharacter();

		return $transferCode;
	}

	private function getRandomCharacter() {
		$charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return $charSet[random_int( 0, strlen( $charSet ) - 1 )];
	}

}