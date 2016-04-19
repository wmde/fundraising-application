<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\System;

use IMcpCreditcardService_v1_5;
use TNvpServiceDispatcher;
use WMDE\Fundraising\Frontend\DataAccess\McpCreditCardService;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class McpCreditCardServiceTest extends \PHPUnit_Framework_TestCase {

	const TEST_MODE = 1;
	const CARD_NUMBER = '4111111111111111';
	const CARD_CVC2 = '666';
	const CUSTOMER_ID = '3df81b041b2afb7b0eceb08b04e926b5';
	const CARD_EXPIRY_MONTH = '11';
	const CARD_EXPIRY_YEAR = '2020';

	/** @var IMcpCreditcardService_v1_5 */
	private $dispatcher;

	private $accessKey;
	private $projectId;

	public function setUp() {
		$config = TestEnvironment::newInstance( [] )->getConfig();
		$this->accessKey = $config['creditcard']['access-key'];
		$this->projectId = $config['creditcard']['project-id'];

		$emailAddress = 'test.this@email.address';
		$this->customerId = md5( $emailAddress );
		$firstName = 'Karl-Walter';
		$lastName = 'Musterhans';

		$this->dispatcher = $this->newDispatcher();

		$this->dispatcher->customerCreate(
			$this->accessKey, self::TEST_MODE, self::CUSTOMER_ID, null, $firstName, $lastName
		);

		$this->dispatcher->creditcardDataSet(
			$this->accessKey, self::TEST_MODE, self::CUSTOMER_ID, self::CARD_NUMBER, self::CARD_EXPIRY_YEAR, self::CARD_EXPIRY_MONTH
		);

		$sessionId = $this->createSession( 250, 'EUR', 'My Title', 'My Pay Text', '127.0.0.1' );
		$this->dispatcher->transactionPurchase( $this->accessKey, self::TEST_MODE, $sessionId, self::CARD_CVC2 );
	}

	private function createSession( $amountInCents, $currencyCode, $title, $payText, $localIpAddress ): string {
		$result = $this->dispatcher->sessionCreate(
			$this->accessKey, self::TEST_MODE, self::CUSTOMER_ID, null, $this->projectId, null, null, null, $amountInCents,
			$currencyCode, $title, $payText, $localIpAddress, false
		);
		return $result['sessionId'];
	}

	public function tearDown() {
		$this->dispatcher->resetTest( $this->accessKey, self::TEST_MODE );
	}

	public function testGivenValidCustomerId_expirationDateIsRetrieved() {
		$service = new McpCreditCardService( $this->dispatcher, $this->accessKey, true );
		$creditCardInfo = $service->getExpirationDate( self::CUSTOMER_ID );
		$this->assertSame( (int)self::CARD_EXPIRY_MONTH, $creditCardInfo->getMonth() );
		$this->assertSame( (int)self::CARD_EXPIRY_YEAR, $creditCardInfo->getYear() );
	}

	/**
	 * @return IMcpCreditcardService_v1_5
	 */
	private function newDispatcher() {
		return new TNvpServiceDispatcher(
			'IMcpCreditcardService_v1_5',
			'https://sipg.micropayment.de/public/creditcard/v1.5/nvp/'
		);
	}

}