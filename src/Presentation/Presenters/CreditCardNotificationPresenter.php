<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\DonationContext\UseCases\NotificationResponse;

class CreditCardNotificationPresenter {

	private const VALUE_ASSIGNMENT = '=';
	private const ARG_SEPARATOR = "\n";

	public function __construct( private readonly string $returnUrl ) {
	}

	public function present( NotificationResponse $response, string $donationId, string $accessToken ): string {
		if ( $response->hasErrors() ) {
			return $this->render( [
				'status' => 'error',
				'msg' => $response->getMessage()
			] );
		}

		return $this->render( [
			'status' => 'ok',
			'url' => $this->returnUrl . '?' . http_build_query( [
					'id' => $donationId,
					'accessToken' => $accessToken
				] ),
			'target' => '_top',
			'forward' => '1',
		] );
	}

	/**
	 * Response format expected by 3rd party
	 *
	 * @see https://www.micropayment.de/help/documentation/
	 *
	 * @param array<string, string> $result
	 * @return string
	 */
	private function render( array $result ): string {
		return implode(
			'',
			array_map(
				static function ( $key, $value ): string {
					return $key . self::VALUE_ASSIGNMENT . $value . self::ARG_SEPARATOR;
				},
				array_keys( $result ),
				array_values( $result )
			)
		);
	}
}
