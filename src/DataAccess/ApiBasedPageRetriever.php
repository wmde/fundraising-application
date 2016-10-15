<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\Api\UsageException;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\Infrastructure\PageRetriever;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiBasedPageRetriever implements PageRetriever {

	const MW_COMMENT_PATTERNS = [
		'/<!--\s*NewPP limit report.*?-->/s' => '',
		'/<!--\s*Transclusion expansion time report.*?-->/s' => '',
		'/<!--\s*Saved in parser cache with key.*?-->/s' => ''
	];

	private $api;
	private $apiUser;
	private $logger;
	private $pageTitlePrefix;

	public function __construct( MediawikiApi $api, ApiUser $apiUser, LoggerInterface $logger, string $pageTitlePrefix ) {
		$this->api = $api;
		$this->apiUser = $apiUser;
		$this->logger = $logger;
		$this->pageTitlePrefix = $pageTitlePrefix;
	}

	/**
	 * @param string $pageTitle
	 * @param string $action
	 * @throws \RuntimeException if the value of $action is not supported
	 * @return string
	 */
	public function fetchPage( string $pageTitle, string $action = PageRetriever::MODE_RENDERED ): string {
		$normalizedPageName = $this->normalizePageName( $this->getPrefixedPageTitle( $pageTitle ) );

		$this->logger->debug( __METHOD__ . ': pageTitle', [ $normalizedPageName ] );

		if ( !$this->api->isLoggedin() ) {
			$this->doLogin();
		}

		$content = $this->retrieveContent( $normalizedPageName, $action );

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
			case 'render':
				return $this->retrieveRenderedPage( $pageTitle );
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

		if ( !empty( $response['parse']['text']['*'] ) ) {
			return $this->cleanupWikiHtml( $response['parse']['text']['*'] );
		}
		return null;
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

	private function cleanupWikiHtml( string $text ): string {
		return rtrim(
			preg_replace(
				array_keys( self::MW_COMMENT_PATTERNS ),
				array_values( self::MW_COMMENT_PATTERNS ),
				$text
			)
		);
	}

	private function normalizePageName( string $title ): string {
		return ucfirst( str_replace( ' ', '_', trim( $title ) ) );
	}

	private function getPrefixedPageTitle( string $pageTitle ): string {
		return $this->pageTitlePrefix . $pageTitle;
	}

}
