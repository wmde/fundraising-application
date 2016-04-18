<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use IMcpCreditcardService_v1_5;
use TNvpServiceDispatcher;
use WMDE\Fundraising\Frontend\Infrastructure\CreditCardExpiry;
use WMDE\Fundraising\Frontend\Infrastructure\CreditCardExpiryFetchingException;
use WMDE\Fundraising\Frontend\Infrastructure\CreditCardService;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class McpCreditCardService implements CreditCardService {

	private $microPaymentDispatcher;
	private $accessKey;

	/**
	 * @param IMcpCreditcardService_v1_5|TNvpServiceDispatcher $microPaymentDispatcher
	 * @param string $accessKey
	 */
	public function __construct( $microPaymentDispatcher, string $accessKey ) {
		$this->microPaymentDispatcher = $microPaymentDispatcher;
		$this->accessKey = $accessKey;
	}

	/**
	 * @param string $customerId
	 * @return CreditCardExpiry
	 * @throws CreditCardExpiryFetchingException
	 */
	public function getExpirationDate( string $customerId ): CreditCardExpiry {
		try {
			$customerData = $this->microPaymentDispatcher->creditcardDataGet( $this->accessKey, null, $customerId );
		}
		catch ( \Exception $ex ) {
			throw new CreditCardExpiryFetchingException( 'MCP-API: Request failed', $ex );
		}

		try {
			$expiryDate = new CreditCardExpiry(
				(int)$customerData['expiryMonth'],
				(int)$customerData['expiryYear']
			);
		}
		catch ( \InvalidArgumentException $ex ) {
			throw new CreditCardExpiryFetchingException( 'Malformed expiry date', $ex );
		}

		return $expiryDate;
	}

}
