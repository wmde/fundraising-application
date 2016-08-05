<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Infrastructure\ArrayBasedStringList;
use WMDE\Fundraising\Frontend\Infrastructure\StringList;

/**
 * @licence GNU GPL v2+
 * @author Christoph Fischer < christoph.fischer@wikimedia.de >
 */
class TextPolicyValidator {

	private $badWords;
	private $whiteWords;

	const CHECK_URLS = 1;
	const CHECK_BADWORDS = 4;
	const IGNORE_WHITEWORDS = 8;

	// FIXME: this should be factored out as it (checkdnsrr) depends on internets
	// Could use an URL validation strategy
	const CHECK_URLS_DNS = 2;

	public function __construct( StringList $badWords = null, StringList $whiteWords = null ) {
		$this->badWords = $badWords ?? new ArrayBasedStringList( [] );
		$this->whiteWords = $whiteWords ?? new ArrayBasedStringList( [] );
	}

	/**
	 * @return string[]
	 */
	private function getBadWords(): array {
		return $this->badWords->toArray();
	}

	/**
	 * @return string[]
	 */
	private function getWhiteWords(): array {
		return $this->whiteWords->toArray();
	}

	/**
	 * @param string $text
	 *
	 * @return bool
	 */
	public function textIsHarmless( string $text ): bool {
		return $this->hasHarmlessContent(
			$text,
			self::CHECK_BADWORDS
			| self::IGNORE_WHITEWORDS
			| self::CHECK_URLS
		);
	}

	public function hasHarmlessContent( string $text, int $flags ): bool {
		$ignoreWhiteWords = (bool) ( $flags & self::IGNORE_WHITEWORDS );

		if ( $flags & self::CHECK_URLS ) {
			$testWithDNS = (bool) ( $flags & self::CHECK_URLS_DNS );

			if ( $this->hasUrls( $text, $testWithDNS, $ignoreWhiteWords ) ) {
				return false;
			}
		}

		if ( $flags & self::CHECK_BADWORDS ) {
			if ( count( $this->getBadWords() ) > 0 && $this->hasBadWords( $text, $ignoreWhiteWords ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string[] $newBadWordsArray
	 */
	public function addBadWordsFromArray( array $newBadWordsArray ) {
		$this->badWords = new ArrayBasedStringList( array_merge( $this->getBadWords(), $newBadWordsArray ) );
	}

	/**
	 * @param string[] $newWhiteWordsArray
	 */
	public function addWhiteWordsFromArray( array $newWhiteWordsArray ) {
		$this->whiteWords = new ArrayBasedStringList( array_merge( $this->getWhiteWords(), $newWhiteWordsArray ) );
	}

	private function hasBadWords( string $text, bool $ignoreWhiteWords ): bool {
		$badMatches = $this->getMatches( $text, $this->getBadWords() );

		if ( $ignoreWhiteWords ) {
			$whiteMatches = $this->getMatches( $text, $this->getWhiteWords() );

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
		return in_array( strtolower( $word ), array_map( 'strtolower', $this->getWhiteWords() ) );
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
			return '';
		}

		if ( isset( $parsedUrl['host'] ) ) {
			return $parsedUrl['host'];
		}

		return $parsedUrl['path'];
	}

	private function composeRegex( array $wordArray ): string {
		return '#(.*?)(' . implode( '|', $wordArray ) . ')#i';
	}

}
