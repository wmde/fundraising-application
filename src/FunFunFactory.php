<?php

namespace WMDE\Fundraising\Frontend;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use FileFetcher\FileFetcher;
use FileFetcher\SimpleFileFetcher;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Twig_Environment;
use Twig_Loader_Filesystem;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\InMemoryCommentRepository;
use WMDE\Fundraising\Frontend\PageRetriever\ApiBasedPageRetriever;
use WMDE\Fundraising\Frontend\PageRetriever\PageRetriever;
use WMDE\Fundraising\Frontend\Presenters\DisplayPagePresenter;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\DisplayPageUseCase;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageContentModifier;
use WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase;
use WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase;
use WMDE\Fundraising\Store\Factory as StoreFactory;
use WMDE\Fundraising\Store\Installer;

/**
 * @licence GNU GPL v2+
 */
class FunFunFactory {

	private $config;

	private $connection;
	private $fileFetcher;

	/**
	 * @param array $config
	 * - db: DBAL connection parameters
	 * - cms-wiki-url
	 * - cms-wiki-api-url
	 * - cms-wiki-user
	 * - cms-wiki-password
	 * - enable-twig-cache: boolean
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function getConnection(): Connection {
		if ( $this->connection === null ) {
			$this->connection = $this->newConnection();
		}

		return $this->connection;
	}

	private function newConnection(): Connection {
		return DriverManager::getConnection( $this->config['db'] );
	}

	public function newInstaller(): Installer {
		return ( new StoreFactory( $this->getConnection() ) )->newInstaller();
	}

	public function newValidateEmailUseCase(): ValidateEmailUseCase {
		return new ValidateEmailUseCase();
	}

	public function newListCommentsUseCase(): ListCommentsUseCase {
		return new ListCommentsUseCase( $this->newCommentRepository() );
	}

	private function newCommentRepository(): CommentRepository {
		return new InMemoryCommentRepository( [] ); // TODO
	}

	public function newDisplayPageUseCase(): DisplayPageUseCase {
		return new DisplayPageUseCase(
			$this->newPageRetriever(),
			$this->newPageContentModifier()
		);
	}

	public function newDisplayPagePresenter(): DisplayPagePresenter {
		return new DisplayPagePresenter( new TwigTemplate(
			$this->newTwig(),
			'DisplayPageLayout.twig'
		) );
	}

	private function newTwig() {
		$options = [];

		if ( $this->config['enable-twig-cache'] ) {
			$options['cache'] = __DIR__ . '/../app/cache';
		}

		return new Twig_Environment(
			new Twig_Loader_Filesystem( __DIR__ . '/../app/templates' ),
			$options
		);
	}

	private function newPageRetriever(): PageRetriever {
		return new ApiBasedPageRetriever(
			new MediawikiApi(
				$this->config['cms-wiki-api-url']
				// TODO: inject guzzle Client so tests can replace it
			),
			new ApiUser( $this->config['cms-wiki-user'], $this->config['cms-wiki-password'] ),
			$this->newLogger()
		);
	}

	private function newLogger(): LoggerInterface {
		return new NullLogger(); // TODO
	}

	private function newPageContentModifier(): PageContentModifier {
		return new PageContentModifier(
			$this->newLogger()
		);
	}

	private function getFileFetcher(): FileFetcher {
		if ( $this->fileFetcher === null ) {
			$this->fileFetcher = new SimpleFileFetcher();
		}

		return $this->fileFetcher;
	}

	/**
	 * Should only be used by test setup code!
	 */
	public function setFileFetcher( FileFetcher $fileFetcher ) {
		$this->fileFetcher = $fileFetcher;
	}

}