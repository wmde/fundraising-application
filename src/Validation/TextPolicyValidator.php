<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Validation;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class TextPolicyValidator {

	private $badWordsArray = [];
	private $whiteWordsArray = [];

	const CHECK_URLS = 1;
	const CHECK_URLS_DNS = 2;
	const CHECK_BADWORDS = 4;
	const IGNORE_WHITEWORDS = 8;

	public function hasHarmlessContent( string $text, int $flags ): bool {
		$ignoreWhiteWords = (bool) ( $flags & self::IGNORE_WHITEWORDS );

		if ( $flags & self::CHECK_URLS ) {
			$testWithDNS = (bool) ( $flags & self::CHECK_URLS_DNS );

			if ( $this->hasUrls( $text, $testWithDNS, $ignoreWhiteWords ) ) {
				return false;
			}
		}

		if ( $flags & self::CHECK_BADWORDS ) {
			if ( count( $this->badWordsArray ) > 0 && $this->hasBadWords( $text, $ignoreWhiteWords ) ) {
				return false;
			}
		}

		return true;
	}

	public function addBadWordsFromFile( string $badWordsFilePath ) {
		$newBadWordsArray = file( $badWordsFilePath, FILE_IGNORE_NEW_LINES );
		$this->addBadWordsFromArray( $newBadWordsArray );
	}

	public function addBadWordsFromString( string $newBadWordsString ) {
		$this->addBadWordsFromArray( explode( '|', $newBadWordsString ) );
	}

	/**
	 * @param string[] $newBadWordsArray
	 */
	public function addBadWordsFromArray( array $newBadWordsArray ) {
		$this->badWordsArray = array_merge( $this->badWordsArray, $newBadWordsArray );
	}

	public function addWhiteWordsFromFile( string $whiteWordsFilePath ) {
		$newWhiteWordsArray = file( $whiteWordsFilePath, FILE_IGNORE_NEW_LINES );
		$this->addWhiteWordsFromArray( $newWhiteWordsArray );
	}

	public function addWhiteWordsFromString( string $newWhiteWordsString ) {
		$this->addWhiteWordsFromArray( explode( '|', $newWhiteWordsString ) );
	}

	/**
	 * @param string[] $newWhiteWordsArray
	 */
	public function addWhiteWordsFromArray( array $newWhiteWordsArray ) {
		$this->whiteWordsArray = array_merge( $this->whiteWordsArray, $newWhiteWordsArray );
	}

	private function hasBadWords( string $text, bool $ignoreWhiteWords ): bool {
		$badMatches = $this->getMatches( $text, $this->badWordsArray );

		if ( $ignoreWhiteWords ) {
			$whiteMatches = $this->getMatches( $text, $this->whiteWordsArray );

			if ( count( $whiteMatches ) > 0 ) {
				return $this->hasBadWordNotMatchingWhiteWords( $badMatches, $whiteMatches );
			}

		}

		return count( $badMatches ) > 0;
	}

	private function getMatches( string $text, array $wordArray ): array {
		$matches = [];
		preg_match_all( $this->composeRegex( $wordArray ), $text, $matches );
		return $matches[0];
	}

	private function hasBadWordNotMatchingWhiteWords( array $badMatches, array $whiteMatches ):bool {
		return count(
			array_udiff( $badMatches, $whiteMatches, function( $badMatch, $whiteMatch ) {
				return !preg_match( $this->composeRegex( [ $badMatch ] ), $whiteMatch );
			} )
		) > 0;
	}

	private function wordMatchesWhiteWords( string $word ): bool {
		return in_array( strtolower( $word ), array_map( 'strtolower', $this->whiteWordsArray ) );
	}

	private function hasUrls( string $text, bool $testWithDNS, bool $ignoreWhiteWords ): bool {
		// check for obvious URLs
		if ( preg_match( '|https?://www\.[a-z\.0-9]+|i', $text ) || preg_match( '|www\.[a-z\.0-9]+|i', $text ) ) {
			return true;
		}

		// check for non-obvious URLs with dns lookup
		if ( $testWithDNS ) {
			$possibleUrls = $this->extractPossibleUrls( $text );

			foreach ( $possibleUrls as $url ) {
				$host = $this->getHostFromUrl( $url );
				if ( !( $ignoreWhiteWords && $this->wordMatchesWhiteWords( $host ) ) && $this->isExistingDomain( $url ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private function extractPossibleUrls( string $text ): array {
		preg_match_all( '|[a-z\.0-9]+\.[a-z]{2,6}|i', $text, $possibleUrls );
		return $possibleUrls[0];
	}

	private function isExistingDomain( string $host ): bool {
		return checkdnsrr( $host, 'A' );
	}

	private function getHostFromUrl( string $url ): string {
		$parsedUrl = parse_url( 'http://' . $url );

		if ( !$parsedUrl ) {
			return false;
		}

		if ( isset( $parsedUrl['host'] ) ) {
			$host = $parsedUrl['host'];
		} else {
			$host = $parsedUrl['path'];
		}

		return $host;
	}

	private function composeRegex( array $wordArray ): string {
		return '#(.*?)(' . implode( '|', $wordArray ) . ')#i';
	}
}
