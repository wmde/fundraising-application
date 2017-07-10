<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TranslatorTest extends WebRouteTestCase {

	public function testGivenDefinedMessageKey_responseContainsTranslatedMessages(): void {
		$client = $this->createClient(
			[],
			function ( FunFunFactory $factory ) {
				$factory->setTranslator( $this->newTranslator( [ 'page_not_found' => 'Seite nicht gefunden' ], 'de' ) );
			}
		);
		$client->request( 'GET', '/anything' );
		$this->assertContains( 'Seite nicht gefunden', $client->getResponse()->getContent() );
	}

	public function testGivenUndefinedMessageKey_responseContainsMessageKey(): void {
		$client = $this->createClient();
		$client->request( 'GET', '/anything' );
		$this->assertContains( 'page_not_found', $client->getResponse()->getContent() );
	}

	private function newTranslator( array $translatableMessages, string $locale ) {
		$translator = new Translator( $locale );
		$translator->addLoader( 'array', new ArrayLoader() );
		$translator->addResource( 'array', $translatableMessages, $locale );
		return $translator;
	}

}
