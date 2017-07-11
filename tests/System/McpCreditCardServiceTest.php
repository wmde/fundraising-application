<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System;

use IMcpCreditcardService_v1_5;
use TNvpServiceDispatcher;
use WMDE\Fundraising\Frontend\PaymentContext\DataAccess\McpCreditCardService;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class McpCreditCardServiceTest extends \PHPUnit\Framework\TestCase {

	const TEST_MODE = 1;
	const CARD_NUMBER = '4111111111111111';
	const CARD_CVC2 = '666';
	const CARD_EXPIRY_MONTH = '11';
	const CARD_EXPIRY_YEAR = '2020';

	/** @var IMcpCreditcardService_v1_5 */
	private $dispatcher;

	private $accessKey;
	private $projectId;

	private $customerId;

	public function setUp(): void {
		$config = TestEnvironment::newInstance( [] )->getConfig();
		$this->accessKey = $config['creditcard']['access-key'];
		$this->projectId = $config['creditcard']['project-id'];

		if ( $this->accessKey === '' || $this->projectId === '' ) {
			$this->markTestSkipped( 'Need access key and project id to run MCP system tests' );
		}

		$emailAddress = 'test.this@email.address';
		$random = bin2hex( random_bytes( 16 ) );
		$this->customerId = md5( $emailAddress . $random );
		$firstName = 'Karl-Walter';
		$lastName = 'Musterhans';

		$this->dispatcher = $this->newDispatcher();

		$this->dispatcher->customerCreate(
			$this->accessKey, self::TEST_MODE, $this->customerId, null, $firstName, $lastName
		);

		$this->dispatcher->creditcardDataSet(
			$this->accessKey, self::TEST_MODE, $this->customerId, self::CARD_NUMBER, self::CARD_EXPIRY_YEAR, self::CARD_EXPIRY_MONTH
		);

		$sessionId = $this->createSession( 250, 'EUR', 'My Title', 'My Pay Text', '127.0.0.1' );
		$this->dispatcher->transactionPurchase( $this->accessKey, self::TEST_MODE, $sessionId, self::CARD_CVC2 );
	}

	private function createSession( int $amountInCents, string $currencyCode, string $title, string $payText, string $localIpAddress ): string {
		$result = $this->dispatcher->sessionCreate(
			$this->accessKey, self::TEST_MODE, $this->customerId, null, $this->projectId, null, null, null, $amountInCents,
			$currencyCode, $title, $payText, $localIpAddress, false
		);
		return $result['sessionId'];
	}

	public function testGivenValidCustomerId_expirationDateIsRetrieved(): void {
		$service = new McpCreditCardService( $this->dispatcher, $this->accessKey, true );
		$creditCardInfo = $service->getExpirationDate( $this->customerId );
		$this->assertSame( (int)self::CARD_EXPIRY_MONTH, $creditCardInfo->getMonth() );
		$this->assertSame( (int)self::CARD_EXPIRY_YEAR, $creditCardInfo->getYear() );
	}

	private function newDispatcher(): TNvpServiceDispatcher {
		return new TNvpServiceDispatcher(
			'IMcpCreditcardService_v1_5',
			'https://sipg.micropayment.de/public/creditcard/v1.5/nvp/'
		);
	}

}