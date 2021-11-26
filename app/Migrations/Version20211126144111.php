<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211126144111 extends AbstractMigration {
	public function getDescription(): string {
		return 'Add banner impression tables for http import';
	}

	public function up( Schema $schema ): void {
		$table = $schema->createTable( 'banner_tracking' );
		$table->addColumn( 'id', 'integer', [ 'autoincrement' => true ] );
		$table->setPrimaryKey( [ 'id' ] );
		$table->addColumn( 'banner_name', 'string', [ 'length' => 255, 'notnull' => false ] );
		$table->addColumn( 'keyword', 'string', [ 'length' => 255, 'notnull' => false ] );
		$table->addColumn( 'campaign', 'string', [ 'length' => 255, 'notnull' => false ] );
		$table->addIndex( [ 'banner_name' ], 'idx_banner_name' );
		$table->addIndex( [ 'keyword' ], 'idx_keyword_5042748250' );

		$table = $schema->createTable( 'banner_impressions' );
		$table->addColumn( 'id', 'integer', [ 'autoincrement' => true ] );
		$table->setPrimaryKey( [ 'id' ] );
		$table->addColumn( 'banner_tracking_id', 'integer', [ 'notnull' => true ] );
		$table->addForeignKeyConstraint( 'banner_tracking', [ 'banner_tracking_id' ], [ 'id' ] );
		$table->addColumn( 'impression_count', 'integer', [ 'notnull' => true ] );
		$table->addColumn( 'interval_start', 'datetime', [ 'notnull' => false ] );
		$table->addIndex( [ 'interval_start' ], 'idx_interval_start' );
	}

	public function down( Schema $schema ): void {
		$table = $schema->getTable( 'banner_impressions' );
		$table->dropIndex( 'idx_interval_start' );
		$schema->dropTable( 'banner_impressions' );

		$table = $schema->getTable( 'banner_tracking' );
		$table->dropIndex( 'idx_banner_name' );
		$table->dropIndex( 'idx_keyword_5042748250' );
		$schema->dropTable( 'banner_tracking' );
	}
}
