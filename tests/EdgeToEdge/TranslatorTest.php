<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use Mediawiki\Api\MediawikiApi;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class TranslatorTest extends WebRouteTestCase {

	// @codingStandardsIgnoreStart
	protected function onTestEnvironmentCreated( FunFunFactory $factory, array $config ) {
		// @codingStandardsIgnoreEnd
		$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();
		$factory->setMediaWikiApi( $api );
		$factory->setTranslator( $this->newTranslator( [ 'my_translatable_message' => 'this is what you expected' ], 'en' ) );
	}

	public function testGivenDefinedMessageKey_responseContainsTranslatedMessages() {
		$client = $this->createClient( [
			'twig' => [
				'loaders' => [
					'array' => [
						'TranslatedPage' => '<p>{$ \'my_translatable_message\'|trans $}</p>',
					],
				]
			]
		] );
		$client->request( 'GET', '/page/TranslatedPage' );
		$this->assertContains( 'this is what you expected', $client->getResponse()->getContent() );
	}

	public function testGivenUndefinedMessageKey_responseContainsMessageKey() {
		$client = $this->createClient( [
			'twig' => [
				'loaders' => [
					'array' => [
						'TranslatedPage' => '<p>{$ \'my_undefined_message\'|trans $}</p>',
					],
				]
			]
		] );
		$client->request( 'GET', '/page/TranslatedPage' );
		$this->assertContains( 'my_undefined_message', $client->getResponse()->getContent() );
	}

	private function newTranslator( array $translatableMessages, string $locale ) {
		$translator = new Translator( $locale );
		$translator->addLoader( 'array', new ArrayLoader() );
		$translator->addResource( 'array', $translatableMessages, $locale );
		return $translator;
	}

}
