<?php

namespace WMDE\Fundraising\Frontend\Domain;

/**
 * Generates a bank transfer code.
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
interface TransferCodeGenerator {

	public function generateTransferCode(): string;

}
