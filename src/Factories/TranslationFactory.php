<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TranslationFactory {

	public function create( array $loaders, string $locale = 'de_DE' ): Translator {
		$translator = new Translator( $locale, new MessageSelector() );
		foreach ( $loaders as $type => $loader ) {
			$translator->addLoader( $type, $loader );
		}
		return $translator;
	}

	public function newJsonLoader() {
		return new JsonFileLoader();
	}
}