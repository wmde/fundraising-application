<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\Factories;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Tests\Fixtures\CampaignFixture;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeTranslator;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FakeUrlGenerator;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\Frontend\Factories\MailerTemplatingFactory
 * @covers \WMDE\Fundraising\Frontend\Factories\TwigFactory
 */
class MailerTemplatingFactoryTest extends TestCase {

	private const TEMPLATE_DIR = 'mail_templates';

	/**
	 * @var MockObject & ContentProvider
	 */
	private $contentProvider;

	private FunFunFactory $factory;

	protected function setUp(): void {
		parent::setUp();
		$this->factory = $this->getFactory( [
			'mailer-twig' => [ 'loaders' => [ 'filesystem' => [ 'template-dir' => vfsStream::url( self::TEMPLATE_DIR ) ] ] ]
		] );
		$this->contentProvider = $this->createMock( ContentProvider::class );
		$this->factory->setContentProvider( $this->contentProvider );
	}

	public function testTranslationFilters(): void {
		$translator = new FakeTranslator( '[%s]' );
		$this->factory->setMailTranslator( $translator );
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'translation_filters.twig' => '{$ mb_type|membership_type $} | {$ interval|payment_interval $} | {$ p_type|payment_method $}',
		] );
		$this->factory->setMailTemplateDirectory( vfsStream::url( self::TEMPLATE_DIR ) );

		$output = $this->factory->getMailerTemplate( 'translation_filters.twig' )->render( [
			'mb_type' => 'active',
			'interval' => 3,
			'p_type' => 'UEB'
		] );

		$this->assertSame( '[active] | [donation_payment_interval_3] | [UEB]', $output );
	}

	private function getFactory( array $configOverrides = [] ): FunFunFactory {
		$factory = TestEnvironment::newInstance( $configOverrides )->getFactory();
		$factory->setSelectedBuckets( [ CampaignFixture::createBucket() ] );
		return $factory;
	}

	public function testContentProvider(): void {
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'template_with_content.twig' => '{$ mail_content("something", { "donation_id": 45 }) $} end',
			'lorem.html.twig' => 'I am the wrong twig environment. Dragons here!',
			'lorem.twig' => 'More Dragons!'
		] );
		$this->factory->setMailTemplateDirectory( vfsStream::url( self::TEMPLATE_DIR ) );
		$this->contentProvider->method( 'getMail' )
			->with( 'something', [ 'donation_id' => 45 ] )
			->willReturn( 'you got mail' );

		$output = $this->factory->getMailerTemplate( 'template_with_content.twig' )->render( [] );

		$this->assertSame( 'you got mail end', $output );
	}

	public function testUrlGeneratorFunction(): void {
		$this->factory->setUrlGenerator( new FakeUrlGenerator() );
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'url_output.twig' => '{$ url( "add-donation", { "amount": 10 } ) $}',
		] );
		$this->factory->setMailTemplateDirectory( vfsStream::url( self::TEMPLATE_DIR ) );

		$output = $this->factory->getMailerTemplate( 'url_output.twig' )->render( [] );

		$this->assertSame( 'https://such.a.url/add-donation?amount=10', $output );
	}

	public function testGlobalVariablesAreSet(): void {
		vfsStream::setup( self::TEMPLATE_DIR, null, [
			'globals.twig' => '{$ day_of_the_week $}',
		] );
		$this->factory->setMailTemplateDirectory( vfsStream::url( self::TEMPLATE_DIR ) );

		$output = $this->factory->getMailerTemplate( 'globals.twig' )->render( [] );

		$this->assertNotEmpty( $output );
	}
}
