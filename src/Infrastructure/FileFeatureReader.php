<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use FileFetcher\FileFetcher;
use FileFetcher\FileFetchingException;
use Psr\Log\LoggerInterface;
use WMDE\Fundraising\Frontend\FeatureToggle\Feature;
use WMDE\Fundraising\Frontend\FeatureToggle\FeatureReader;

class FileFeatureReader implements FeatureReader {

	public function __construct(
		private readonly FileFetcher $fileFetcher,
		private readonly string $featureFile,
		private readonly LoggerInterface $logger,
	) {
	}

	/**
	 * @return Feature[]
	 */
	public function getFeatures(): array {
		try {
			$content = $this->fileFetcher->fetchFile( $this->featureFile );
		} catch ( FileFetchingException $e ) {
			$this->logger->notice( "Could not read feature file '{$this->featureFile}'" );
			return [];
		}

		if ( !$content ) {
			$this->logger->notice( "Feature file '{$this->featureFile}' was empty" );
			return [];
		}

		try {
			$rawFeatures = json_decode( $content, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			$this->logger->warning( "Feature file '{$this->featureFile}' contained invalid JSON: {$e->getMessage()}", [ 'fileContent' => $content ] );
			return [];
		}

		if ( !is_array( $rawFeatures ) ) {
			$this->logger->warning( "Feature file '{$this->featureFile}' is not a JSON array", [ 'fileContent' => $content ] );
			return [];
		}

		$features = [];
		foreach ( $rawFeatures as $idx => $feature ) {
			if ( !is_array( $feature ) ) {
				$this->logger->warning( "Feature file '{$this->featureFile}' entry #{$idx} should be an object", [ 'rawFeatures' => $rawFeatures ] );
				continue;
			}
			$name = (string)( $feature['name'] ?? '' );
			$active = (bool)( $feature['active'] ?? false );
			if ( !$name ) {
				$this->logger->warning( "Feature file '{$this->featureFile}' entry #{$idx} object must have a name property", [ 'rawFeatures' => $rawFeatures ] );
				continue;
			}
			$features[] = new Feature( $name, $active );
		}

		return $features;
	}

}
