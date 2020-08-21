<?php

declare( strict_types = 1 );

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200812000000 extends AbstractMigration {

	public function up( Schema $schema ): void {
		$this->addSql(
			'CREATE TABLE bucket_log(
    				id INT AUTO_INCREMENT NOT NULL,
    				external_id INT NOT NULL,
    				event_name VARCHAR(24) NOT NULL,
    				date DATETIME NOT NULL,
    				PRIMARY KEY(id)
			   ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
		);

		$this->addSql(
			'CREATE TABLE bucket_log_bucket(
    				id INT AUTO_INCREMENT NOT NULL,
    				bucket_log_id INT NOT NULL,
    				name VARCHAR(24) NOT NULL,
    				campaign VARCHAR(24) NOT NULL,
    				INDEX idx_bucket_log (bucket_log_id),
    				PRIMARY KEY(id),
    				FOREIGN KEY (bucket_log_id) REFERENCES bucket_log(id)
			   ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
		);
	}

	public function down( Schema $schema ): void {
		$this->addSql( 'DROP TABLE bucket_log_bucket' );
		$this->addSql( 'DROP TABLE bucket_log' );
	}
}
