<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230823082138 extends AbstractMigration {
	public function getDescription(): string {
		return 'Create Old-Style Authentication Tokens Table';
	}

	public function up( Schema $schema ): void {
		$table = $schema->createTable( 'legacy_auth_tokens' );
		$table->addColumn( 'id', 'integer', [ 'unsigned' => true ] );
		$table->addColumn( 'authentication_context', 'string', [ 'length' => 16 ] );
		$table->addColumn( 'access_token', 'string', [ 'length' => 64 ] );
		$table->addColumn( 'update_token', 'string', [ 'length' => 64 ] );
		$table->addColumn( 'update_token_expiry', 'datetime', [ 'notnull' => false ] );
		$table->setPrimaryKey( [ 'id', 'authentication_context' ] );
		$table->addIndex( [ 'access_token' ], 'access_token_idx' );
		$table->addIndex( [ 'update_token' ], 'update_token_idx' );
	}

	public function down( Schema $schema ): void {
		$schema->dropTable( 'legacy_auth_tokens' );
	}
}
