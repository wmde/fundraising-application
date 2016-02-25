<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Model\Donation
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNonDirectDebitDonation_cancellationFails() {
		$donation = new Donation();
		$donation->setPaymentType( PaymentType::CREDIT_CARD );

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

}