<?php

namespace WMDE\Fundraising\Frontend;

use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

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