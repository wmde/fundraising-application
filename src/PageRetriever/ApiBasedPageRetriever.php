<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\PageRetriever;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\Api\UsageException;
use Psr\Log\LoggerInterface;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiBasedPageRetriever implements PageRetriever {

	private $api;
	private $apiUser;
	private $logger;

	public function __construct( MediawikiApi $api, ApiUser $apiUser, LoggerInterface $logger ) {
		$this->api = $api;
		$this->apiUser = $apiUser;
		$this->logger = $logger;
	}

	public function fetchPage( string $pageTitle ): string {
		$this->logger->debug( __METHOD__ . ': pageTitle', [ $pageTitle ] );

		if ( !$this->api->isLoggedin() ) {
			$this->doLogin();
		}

		$content = $this->retrieveRenderedPage( $pageTitle );

		if ( $content === false || $content === null ) {
			$this->logger->debug( __METHOD__ . ': fail, got non-value', [ $content ] );
			return '';
		}

		return $content;
	}

	private function doLogin() {
		$this->api->login( $this->apiUser );
	}

	private function retrieveRenderedPage( $pageTitle ) {
		$params = [
			'page' => $pageTitle,
			'prop' => 'text'
		];

		try {
			$response = $this->api->postRequest( new SimpleRequest( 'parse', $params ) );
		} catch ( UsageException $e ) {
			return false;
		}

		return $response['parse']['text']['*'];
	}

}
