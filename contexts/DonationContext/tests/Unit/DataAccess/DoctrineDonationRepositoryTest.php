<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DonationContext\Tests\Unit\DataAccess;

use ReflectionMethod;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\CreditCardPayment;
use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\SofortPayment;

/**
 * @covers \WMDE\Fundraising\Frontend\DonationContext\DataAccess\DoctrineDonationRepository
 */
class DoctrineDonationRepositoryTest extends TestCase {

	public function testGetBankTransferCode(): void {
		$repo = new DoctrineDonationRepository( $this->createMock( EntityManager::class ) );

		$method = new ReflectionMethod( $repo, 'getBankTransferCode' );
		$method->setAccessible( true );

		$paymentMethod = new SofortPayment( 'fff' );
		$this->assertSame( 'fff', $method->invoke( $repo, $paymentMethod ) );
		$paymentMethod = new BankTransferPayment( 'ggg' );
		$this->assertSame( 'ggg', $method->invoke( $repo, $paymentMethod ) );
		$paymentMethod = new CreditCardPayment( null );
		$this->assertSame( '', $method->invoke( $repo, $paymentMethod ) );
	}
}
