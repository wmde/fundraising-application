<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\Api\UsageException;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Domain\PageRetriever;

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

	/**
	 * @param string $pageTitle
	 * @param string $action
	 * @throws \RuntimeException if the value of $action is not supported
	 * @return string
	 */
	public function fetchPage( string $pageTitle, string $action = 'render' ): string {
		$this->logger->debug( __METHOD__ . ': pageTitle', [ $pageTitle ] );

		if ( !$this->api->isLoggedin() ) {
			$this->doLogin();
		}

		$content = $this->retrieveContent( $pageTitle, $action );

		if ( $content === false || $content === null ) {
			$this->logger->debug( __METHOD__ . ': fail, got non-value', [ $content ] );
			return '';
		}

		return $content;
	}

	private function doLogin() {
		$this->api->login( $this->apiUser );
	}

	/**
	 * @param string $pageTitle
	 * @param string $action
	 * @return string|bool retrieved content or false on error
	 */
	private function retrieveContent( string $pageTitle, string $action ) {
		switch ( $action ) {
			case 'raw':
				return $this->retrieveWikiText( $pageTitle );
				break;
			case 'render':
				return $this->retrieveRenderedPage( $pageTitle );
				break;
			default:
				throw new \RuntimeException( 'Action "' . $action . '" not supported' );
				break;
		}
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

	private function retrieveWikiText( $pageTitle ) {
		$params = [
			'titles' => $pageTitle,
			'prop' => 'revisions',
			'rvprop' => 'content'
		];

		try {
			$response = $this->api->postRequest( new SimpleRequest( 'query', $params ) );
		} catch ( UsageException $e ) {
			return false;
		}

		if ( !is_array( $response['query']['pages'] ) ) {
			return false;
		}
		$page = reset( $response['query']['pages'] );

		return $page['revisions'][0]['*'];
	}

}
