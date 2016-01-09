<?php

namespace WMDE\Fundraising\Frontend;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use FileFetcher\FileFetcher;
use FileFetcher\SimpleFileFetcher;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;
use WMDE\Fundraising\Frontend\Domain\InMemoryCommentRepository;
use WMDE\Fundraising\Frontend\PageRetriever\ActionBasedPageRetriever;
use WMDE\Fundraising\Frontend\PageRetriever\PageRetriever;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\DisplayPageUseCase;
use WMDE\Fundraising\Frontend\UseCases\DisplayPage\PageContentModifier;
use WMDE\Fundraising\Frontend\UseCases\ListComments\ListCommentsUseCase;
use WMDE\Fundraising\Frontend\UseCases\ValidateEmail\ValidateEmailUseCase;
use WMDE\Fundraising\Store\Factory as StoreFactory;
use WMDE\Fundraising\Store\Installer;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FunFunFactory {

	private $config;

	private $connection;
	private $fileFetcher;

	/**
	 * @param array $config
	 * - db: DBAL connection parameters
	 * - cms-wiki-url
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

	private function newPageRetriever(): PageRetriever {
		return new ActionBasedPageRetriever(
			$this->config['cms-wiki-url'],
			$this->newLogger(),
			$this->getFileFetcher()
		);
	}

	private function newLogger(): LoggerInterface {
		return new NullLogger();
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