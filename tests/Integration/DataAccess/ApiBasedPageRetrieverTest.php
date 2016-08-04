<?php


namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\UsageException;
use Mediawiki\Api\SimpleRequest;
use WMDE\Fundraising\Frontend\ApplicationContext\DataAccess\ApiBasedPageRetriever;
use WMDE\Fundraising\Frontend\ApplicationContext\Infrastructure\PageRetriever;
use WMDE\Fundraising\Frontend\Tests\Fixtures\LoggerSpy;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

class ApiBasedPageRetrieverTest extends \PHPUnit_Framework_TestCase {

	const PAGE_PREFIX = 'Web:SpendenseiteTestSkin/';

	private $api;
	private $apiUser;
	private $logger;

	/*
	 * @var ApiBasedPageRetriever
	 */
	private $pageRetriever;

	public function setUp() {
		$this->api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();
		$this->apiUser = $this->getMockBuilder( ApiUser::class )->disableOriginalConstructor()->getMock();
		$this->logger = new LoggerSpy();
		$this->pageRetriever = new ApiBasedPageRetriever( $this->api, $this->apiUser, $this->logger, self::PAGE_PREFIX );
	}

	public function testRetrieverReturnsApiResultInRenderMode() {
		$this->api->method( 'isLoggedin' )->willReturn( true );
		$this->api->method( 'postRequest' )->willReturn( TestEnvironment::getJsonTestData( 'mwApiUnicornsPage.json' ) );

		$expectedContent = '<p>Pink fluffy unicorns dancing on rainbows</p>';
		$this->assertSame( $expectedContent, $this->pageRetriever->fetchPage( 'Unicorns' ) );
	}

	public function testRetrieverReturnsApiResultInRawMode() {
		$this->api->method( 'isLoggedin' )->willReturn( true );
		$this->api->method( 'postRequest' )->willReturn( TestEnvironment::getJsonTestData( 'mwApiNo_CatsQuery.json' ) );

		$expectedContent = "Nyan\nGarfield\nFelix da House";
		$pageName = 'Web:Spendenseite-HK2013/test/No Cats';
		$this->assertSame( $expectedContent, $this->pageRetriever->fetchPage( $pageName, PageRetriever::MODE_RAW ) );
	}

	/**
	 * The Mediawiki API does a json_decode which will return null if the request is not valid JSON
	 */
	public function testGivenApiReturnsNull_failureIsLogged() {
		$this->api->method( 'isLoggedin' )->willReturn( true );
		$this->api->method( 'postRequest' )->willReturn( null );

		$this->pageRetriever->fetchPage( 'test page' );

		$expectedLogMessage = 'WMDE\Fundraising\Frontend\ApplicationContext\DataAccess\ApiBasedPageRetriever::fetchPage: fail, got non-value';
		$this->logger->assertCalledWithMessage( $expectedLogMessage, $this );
	}

	public function testGivenApiThrowsUsageException_failureIsLogged() {
		$this->api->method( 'isLoggedin' )->willReturn( true );
		$this->api->method( 'postRequest' )->will( $this->throwException( new UsageException() ) );

		$this->pageRetriever->fetchPage( 'test page' );

		$expectedLogMessage = 'WMDE\Fundraising\Frontend\ApplicationContext\DataAccess\ApiBasedPageRetriever::fetchPage: fail, got non-value';
		$this->logger->assertCalledWithMessage( $expectedLogMessage, $this );
	}

	public function testMediaWikiPerformanceCommentsAreRemoved() {
		$this->api->method( 'isLoggedin' )->willReturn( true );
		$this->api->method( 'postRequest' )->willReturn( TestEnvironment::getJsonTestData( 'mwApiPerformanceCommentPage.json' ) );

		$expectedContent = '<p>Pink fluffy unicorns dancing on rainbows</p>';
		$this->assertSame( $expectedContent, $this->pageRetriever->fetchPage( 'PerformanceComment' ) );
	}

	public function testGivenPagenameWithSpaces_theyAreTrimmedAndReplacedWithUnderscores() {
		$this->api->method( 'isLoggedin' )->willReturn( true );
		$this->api->expects( $this->once() )
			->method( 'postRequest' )
			->with( new SimpleRequest( 'parse', [
				'page' => self::PAGE_PREFIX . 'No_Spaces_Allowed',
				'prop' => 'text'
			] ) );

		$this->pageRetriever->fetchPage( 'No Spaces Allowed ' );
	}

	public function testWhenMultiplePagesAreRetrieved_apiAuthenticatesOnlyForFirstPage() {
		$this->api->method( 'isLoggedin' )->will( $this->onConsecutiveCalls( false, true, true ) );
		$this->api->expects( $this->once() )
			->method( 'login' )
			->with( $this->apiUser );
		$this->api->method( 'postRequest' )->willReturn( TestEnvironment::getJsonTestData( 'mwApiUnicornsPage.json' ) );

		$this->pageRetriever->fetchPage( 'Unicorns' );
		$this->pageRetriever->fetchPage( 'Lollipops' );
		$this->pageRetriever->fetchPage( 'Rainbows' );
	}
}
