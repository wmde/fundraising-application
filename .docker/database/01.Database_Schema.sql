CREATE TABLE IF NOT EXISTS donation_moderation_reason (id INT AUTO_INCREMENT NOT NULL, moderation_identifier VARCHAR(50) NOT NULL, source VARCHAR(32) DEFAULT '' NOT NULL, INDEX d_mr_identifier (moderation_identifier, source), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS donation_notification_log (donation_id INT NOT NULL, PRIMARY KEY(donation_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS last_generated_donation_id (id INT AUTO_INCREMENT NOT NULL, donation_id INT UNSIGNED DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS spenden (id INT NOT NULL, status CHAR(1) DEFAULT 'N' NOT NULL, name VARCHAR(250) DEFAULT NULL, ort VARCHAR(250) DEFAULT NULL, email VARCHAR(250) DEFAULT NULL, info TINYINT(1) DEFAULT 0 NOT NULL, bescheinigung TINYINT(1) DEFAULT NULL, eintrag VARCHAR(250) DEFAULT '' NOT NULL, betrag VARCHAR(250) DEFAULT NULL, periode SMALLINT DEFAULT 0 NOT NULL, zahlweise CHAR(3) DEFAULT 'BEZ' NOT NULL, kommentar LONGTEXT DEFAULT '' NOT NULL, ueb_code VARCHAR(32) DEFAULT '' NOT NULL, data LONGTEXT DEFAULT NULL, source VARCHAR(250) DEFAULT NULL, remote_addr VARCHAR(250) DEFAULT '' NOT NULL, hash VARCHAR(250) DEFAULT NULL, is_public TINYINT(1) DEFAULT 0 NOT NULL, dt_new DATETIME NOT NULL, dt_del DATETIME DEFAULT NULL, dt_exp DATETIME DEFAULT NULL, dt_gruen DATETIME DEFAULT NULL, dt_backup DATETIME DEFAULT NULL, payment_id INT UNSIGNED DEFAULT 0 NOT NULL, FULLTEXT INDEX d_email (email), FULLTEXT INDEX d_name (name), FULLTEXT INDEX d_ort (ort), INDEX d_dt_new (dt_new, is_public), INDEX d_zahlweise (zahlweise, dt_new), INDEX d_dt_gruen (dt_gruen, dt_del), INDEX d_ueb_code (ueb_code), INDEX d_dt_backup (dt_backup), INDEX d_status (status, dt_new), INDEX d_comment_list (is_public, dt_del), INDEX d_payment_id (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS donations_moderation_reasons (donation_id INT NOT NULL, moderation_reason_id INT NOT NULL, INDEX IDX_34094344DC1279C (donation_id), INDEX IDX_3409434A3D4B33C (moderation_reason_id), PRIMARY KEY(donation_id, moderation_reason_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS request (id INT NOT NULL, status SMALLINT DEFAULT 0, donation_id INT DEFAULT NULL, timestamp DATETIME NOT NULL, anrede VARCHAR(16) DEFAULT NULL, firma VARCHAR(100) DEFAULT NULL, titel VARCHAR(16) DEFAULT NULL, name VARCHAR(250) DEFAULT '' NOT NULL, vorname VARCHAR(50) DEFAULT '' NOT NULL, nachname VARCHAR(50) DEFAULT '' NOT NULL, strasse VARCHAR(100) DEFAULT NULL, plz VARCHAR(8) DEFAULT NULL, ort VARCHAR(100) DEFAULT NULL, country VARCHAR(8) DEFAULT '', email VARCHAR(250) DEFAULT '' NOT NULL, phone VARCHAR(30) DEFAULT '' NOT NULL, dob DATE DEFAULT NULL, wikimedium_shipping VARCHAR(255) DEFAULT '' NOT NULL, membership_type VARCHAR(255) DEFAULT 'sustaining' NOT NULL, payment_type VARCHAR(255) DEFAULT 'BEZ' NOT NULL, membership_fee INT DEFAULT 0 NOT NULL, membership_fee_interval SMALLINT DEFAULT 12, account_number VARCHAR(16) DEFAULT '' NOT NULL, bank_name VARCHAR(100) DEFAULT '' NOT NULL, bank_code VARCHAR(16) DEFAULT '' NOT NULL, iban VARCHAR(32) DEFAULT '', bic VARCHAR(32) DEFAULT '', comment LONGTEXT DEFAULT '' NOT NULL, export DATETIME DEFAULT NULL, backup DATETIME DEFAULT NULL, wikilogin TINYINT(1) DEFAULT 0 NOT NULL, tracking VARCHAR(50) DEFAULT NULL, data LONGTEXT DEFAULT NULL, donation_receipt TINYINT(1) DEFAULT NULL, payment_id INT UNSIGNED DEFAULT 0 NOT NULL, FULLTEXT INDEX m_email (email), FULLTEXT INDEX m_name (name), FULLTEXT INDEX m_ort (ort), INDEX m_payment (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS membership_incentive (membership_id INT NOT NULL, incentive_id INT NOT NULL, INDEX IDX_4AE7CF6F1FB354CD (membership_id), INDEX IDX_4AE7CF6FF17F400F (incentive_id), PRIMARY KEY(membership_id, incentive_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS memberships_moderation_reasons (membership_id INT NOT NULL, moderation_reason_id INT NOT NULL, INDEX IDX_50E0C0181FB354CD (membership_id), INDEX IDX_50E0C018A3D4B33C (moderation_reason_id), PRIMARY KEY(membership_id, moderation_reason_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS incentive (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS last_generated_membership_id (id INT AUTO_INCREMENT NOT NULL, membership_id INT UNSIGNED DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS membership_moderation_reason (id INT AUTO_INCREMENT NOT NULL, moderation_identifier VARCHAR(50) NOT NULL, source VARCHAR(32) DEFAULT '' NOT NULL, INDEX m_mr_identifier (moderation_identifier, source), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment_bank_transfer (id INT NOT NULL, payment_reference_code VARCHAR(255) DEFAULT NULL, is_cancelled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8C70B08AE5FE723C (payment_reference_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment_credit_card (id INT NOT NULL, valuation_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', booking_data JSON DEFAULT NULL COMMENT '(DC2Type:json)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment_sofort (id INT NOT NULL, payment_reference_code VARCHAR(255) DEFAULT NULL, valuation_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', transaction_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_15892F55E5FE723C (payment_reference_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment_reference_codes (code VARCHAR(255) NOT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS last_generated_payment_id (id INT AUTO_INCREMENT NOT NULL, payment_id INT UNSIGNED DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment (id INT NOT NULL, amount INT NOT NULL, payment_interval INT NOT NULL, payment_method VARCHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment_paypal_identifier (payment_id INT NOT NULL, identifier_type VARCHAR(1) NOT NULL, subscription_id VARCHAR(255) DEFAULT NULL, transaction_id VARCHAR(255) DEFAULT NULL, order_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(payment_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment_paypal (id INT NOT NULL, parent_payment_id INT DEFAULT NULL, valuation_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', booking_data JSON DEFAULT NULL COMMENT '(DC2Type:json)', transaction_id VARCHAR(36) DEFAULT NULL, INDEX IDX_41AE99DA438027EB (parent_payment_id), INDEX ppl_transaction_id (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS payment_direct_debit (id INT NOT NULL, iban VARCHAR(255) DEFAULT NULL, bic VARCHAR(255) DEFAULT NULL, is_cancelled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS subscription (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(250) DEFAULT '' NOT NULL, export DATETIME DEFAULT NULL, backup DATETIME DEFAULT NULL, status SMALLINT DEFAULT NULL, confirmationCode VARCHAR(32) DEFAULT NULL, tracking VARCHAR(50) DEFAULT NULL, source VARCHAR(50) DEFAULT NULL, createdAt DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS address (id INT AUTO_INCREMENT NOT NULL, salutation VARCHAR(16) DEFAULT NULL, company VARCHAR(100) DEFAULT NULL, title VARCHAR(16) DEFAULT NULL, first_name VARCHAR(50) DEFAULT '' NOT NULL, last_name VARCHAR(50) DEFAULT '' NOT NULL, street VARCHAR(100) DEFAULT NULL, postcode VARCHAR(8) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, country VARCHAR(8) DEFAULT '', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS address_change (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, address_type VARCHAR(10) NOT NULL, external_id INT NOT NULL, external_id_type VARCHAR(10) NOT NULL, export_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, donation_receipt TINYINT(1) NOT NULL, current_identifier VARCHAR(36) NOT NULL, previous_identifier VARCHAR(36) NOT NULL, INDEX ac_export_date (export_date), INDEX IDX_7B0E7B9FF5B7AF75 (address_id), UNIQUE INDEX UNIQ_7B0E7B9FA8954A18 (current_identifier), UNIQUE INDEX UNIQ_7B0E7B9F2EC1D3 (previous_identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS bucket_log (id INT AUTO_INCREMENT NOT NULL, event_name VARCHAR(64) NOT NULL, external_id INT NOT NULL, date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS bucket_log_bucket (id INT AUTO_INCREMENT NOT NULL, bucket_log_id INT DEFAULT NULL, name VARCHAR(24) NOT NULL, campaign VARCHAR(24) NOT NULL, INDEX idx_bucket_log (bucket_log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS geodaten_artikelnr_1001 (id INT AUTO_INCREMENT NOT NULL, BUNDESLAND_NAME VARCHAR(30) DEFAULT NULL, BUNDESLAND_NUTSCODE VARCHAR(3) DEFAULT NULL, REGIERUNGSBEZIRK_NAME VARCHAR(50) DEFAULT NULL, REGIERUNGSBEZIRK_NUTSCODE VARCHAR(5) DEFAULT NULL, KREIS_NAME VARCHAR(50) DEFAULT NULL, KREIS_TYP VARCHAR(40) DEFAULT NULL, KREIS_NUTSCODE VARCHAR(5) DEFAULT NULL, GEMEINDE_NAME VARCHAR(50) DEFAULT NULL, GEMEINDE_TYP VARCHAR(40) DEFAULT NULL, GEMEINDE_AGS VARCHAR(8) DEFAULT NULL, GEMEINDE_RS VARCHAR(20) NOT NULL, GEMEINDE_LAT NUMERIC(8, 5) UNSIGNED DEFAULT NULL, GEMEINDE_LON NUMERIC(8, 5) UNSIGNED DEFAULT NULL, ORT_ID INT UNSIGNED DEFAULT NULL, ORT_NAME VARCHAR(80) DEFAULT NULL, ORT_LAT NUMERIC(8, 5) DEFAULT NULL, ORT_LON NUMERIC(8, 5) DEFAULT NULL, POSTLEITZAHL CHAR(5) NOT NULL, INDEX idx_postcode (POSTLEITZAHL), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS legacy_auth_tokens (id INT NOT NULL, authentication_context VARCHAR(16) NOT NULL, access_token VARCHAR(64) NOT NULL, update_token VARCHAR(64) NOT NULL, update_token_expiry DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX access_token_idx (access_token), INDEX update_token_idx (update_token), PRIMARY KEY(id, authentication_context)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
ALTER TABLE donations_moderation_reasons ADD CONSTRAINT FK_34094344DC1279C FOREIGN KEY (donation_id) REFERENCES spenden (id);
ALTER TABLE donations_moderation_reasons ADD CONSTRAINT FK_3409434A3D4B33C FOREIGN KEY (moderation_reason_id) REFERENCES donation_moderation_reason (id);
ALTER TABLE membership_incentive ADD CONSTRAINT FK_4AE7CF6F1FB354CD FOREIGN KEY (membership_id) REFERENCES request (id);
ALTER TABLE membership_incentive ADD CONSTRAINT FK_4AE7CF6FF17F400F FOREIGN KEY (incentive_id) REFERENCES incentive (id);
ALTER TABLE memberships_moderation_reasons ADD CONSTRAINT FK_50E0C0181FB354CD FOREIGN KEY (membership_id) REFERENCES request (id);
ALTER TABLE memberships_moderation_reasons ADD CONSTRAINT FK_50E0C018A3D4B33C FOREIGN KEY (moderation_reason_id) REFERENCES membership_moderation_reason (id);
ALTER TABLE payment_bank_transfer ADD CONSTRAINT FK_8C70B08AE5FE723C FOREIGN KEY (payment_reference_code) REFERENCES payment_reference_codes (code);
ALTER TABLE payment_bank_transfer ADD CONSTRAINT FK_8C70B08ABF396750 FOREIGN KEY (id) REFERENCES payment (id) ON DELETE CASCADE;
ALTER TABLE payment_credit_card ADD CONSTRAINT FK_E75AF734BF396750 FOREIGN KEY (id) REFERENCES payment (id) ON DELETE CASCADE;
ALTER TABLE payment_sofort ADD CONSTRAINT FK_15892F55E5FE723C FOREIGN KEY (payment_reference_code) REFERENCES payment_reference_codes (code);
ALTER TABLE payment_sofort ADD CONSTRAINT FK_15892F55BF396750 FOREIGN KEY (id) REFERENCES payment (id) ON DELETE CASCADE;
ALTER TABLE payment_paypal_identifier ADD CONSTRAINT FK_D7AFB034C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id);
ALTER TABLE payment_paypal ADD CONSTRAINT FK_41AE99DA438027EB FOREIGN KEY (parent_payment_id) REFERENCES payment_paypal (id);
ALTER TABLE payment_paypal ADD CONSTRAINT FK_41AE99DABF396750 FOREIGN KEY (id) REFERENCES payment (id) ON DELETE CASCADE;
ALTER TABLE payment_direct_debit ADD CONSTRAINT FK_8A755A8ABF396750 FOREIGN KEY (id) REFERENCES payment (id) ON DELETE CASCADE;
ALTER TABLE address_change ADD CONSTRAINT FK_7B0E7B9FF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id);
ALTER TABLE bucket_log_bucket ADD CONSTRAINT FK_953634738DD5EE37 FOREIGN KEY (bucket_log_id) REFERENCES bucket_log (id);
