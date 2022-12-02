<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221118093850 extends AbstractMigration {

	public function getDescription(): string {
		return 'Increase the length of the bucket log event name column';
	}

	public function up( Schema $schema ): void {
		$table = $schema->getTable( 'bucket_log' );
		$table->modifyColumn( 'event_name', [ 'length' => 64 ] );
	}

	public function postUp( Schema $schema ): void {
		$this->connection->executeStatement( 'UPDATE bucket_log SET event_name = "membershipApplicationCreated" WHERE event_name = "membershipApplicationCre"' );
	}

	public function down( Schema $schema ): void {
		$table = $schema->getTable( 'bucket_log' );
		$table->modifyColumn( 'event_name', [ 'length' => 24 ] );
	}
}
