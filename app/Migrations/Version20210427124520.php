<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210427124520 extends AbstractMigration {
	public function getDescription(): string {
		return 'Add Donation confirmation log';
	}

	public function up( Schema $schema ): void {
		$table = $schema->createTable( 'donation_notification_log' );
		$table->addColumn( 'donation_id', 'integer' );
		$table->setPrimaryKey( [ 'donation_id' ] );
		$table->addForeignKeyConstraint( 'spenden', [ 'donation_id' ], [ 'id' ], [ 'onDelete' => 'CASCADE' ] );
	}

	public function down( Schema $schema ): void {
		$schema->dropTable( 'donation_notification_log' );
	}
}
