
CREATE TABLE IF NOT EXISTS `address`
(
	`id`         int(11) NOT NULL AUTO_INCREMENT,
	`salutation` varchar(16) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`company`    varchar(100) COLLATE utf8_unicode_ci         DEFAULT NULL,
	`title`      varchar(16) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`last_name`  varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`street`     varchar(100) COLLATE utf8_unicode_ci         DEFAULT NULL,
	`postcode`   varchar(8) COLLATE utf8_unicode_ci           DEFAULT NULL,
	`city`       varchar(100) COLLATE utf8_unicode_ci         DEFAULT NULL,
	`country`    varchar(8) COLLATE utf8_unicode_ci           DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `address_change`
(
	`id`                  int(11) NOT NULL AUTO_INCREMENT,
	`address_id`          int(11) DEFAULT NULL,
	`address_type`        varchar(10) COLLATE utf8_unicode_ci NOT NULL,
	`external_id`         int(11) NOT NULL,
	`external_id_type`    varchar(10) COLLATE utf8_unicode_ci NOT NULL,
	`export_date`         datetime DEFAULT NULL,
	`created_at`          datetime                            NOT NULL,
	`modified_at`         datetime                            NOT NULL,
	`donation_receipt`    tinyint(1) NOT NULL,
	`current_identifier`  varchar(36) COLLATE utf8_unicode_ci NOT NULL,
	`previous_identifier` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `UNIQ_7B0E7B9FA8954A18` (`current_identifier`),
	UNIQUE KEY `UNIQ_7B0E7B9F2EC1D3` (`previous_identifier`),
	KEY                   `ac_export_date` (`export_date`),
	KEY                   `IDX_7B0E7B9FF5B7AF75` (`address_id`),
	CONSTRAINT `FK_7B0E7B9FF5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `bucket_log`
(
	`id`          int(11) NOT NULL AUTO_INCREMENT,
	`event_name`  varchar(24) COLLATE utf8_unicode_ci NOT NULL,
	`external_id` int(11) NOT NULL,
	`date`        datetime DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `bucket_log_bucket`
(
	`id`            int(11) NOT NULL AUTO_INCREMENT,
	`bucket_log_id` int(11) DEFAULT NULL,
	`name`          varchar(24) COLLATE utf8_unicode_ci NOT NULL,
	`campaign`      varchar(24) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (`id`),
	KEY             `idx_bucket_log` (`bucket_log_id`),
	CONSTRAINT `FK_953634738DD5EE37` FOREIGN KEY (`bucket_log_id`) REFERENCES `bucket_log` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `donation_payment`
(
	`id`           int(11) NOT NULL AUTO_INCREMENT,
	`payment_type` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `donation_payment_sofort`
(
	`id`           int(11) NOT NULL,
	`confirmed_at` datetime DEFAULT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `FK_2DF4845ABF396750` FOREIGN KEY (`id`) REFERENCES `donation_payment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `geodaten_artikelnr_1001`
(
	`id`                        mediumint(6) unsigned NOT NULL DEFAULT '0',
	`BUNDESLAND_NAME`           varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
	`BUNDESLAND_NUTSCODE`       varchar(3) COLLATE utf8_unicode_ci  DEFAULT NULL,
	`REGIERUNGSBEZIRK_NAME`     varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
	`REGIERUNGSBEZIRK_NUTSCODE` varchar(5) COLLATE utf8_unicode_ci  DEFAULT NULL,
	`KREIS_NAME`                varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
	`KREIS_TYP`                 varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
	`KREIS_NUTSCODE`            varchar(5) COLLATE utf8_unicode_ci  DEFAULT NULL,
	`GEMEINDE_NAME`             varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
	`GEMEINDE_TYP`              varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
	`GEMEINDE_AGS`              varchar(8) COLLATE utf8_unicode_ci  DEFAULT NULL,
	`GEMEINDE_RS`               varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	`GEMEINDE_LAT`              decimal(8, 5) unsigned DEFAULT NULL,
	`GEMEINDE_LON`              decimal(8, 5) unsigned DEFAULT NULL,
	`ORT_ID`                    mediumint(8) unsigned DEFAULT NULL,
	`ORT_NAME`                  varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
	`ORT_LAT`                   decimal(8, 5)                       DEFAULT NULL,
	`ORT_LON`                   decimal(8, 5)                       DEFAULT NULL,
	`POSTLEITZAHL`              char(5) COLLATE utf8_unicode_ci     DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `incentive`
(
	`id`   int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `request`
(
	`id`                      int(11) NOT NULL AUTO_INCREMENT,
	`status`                  smallint(6) DEFAULT '0',
	`donation_id`             int(11) DEFAULT NULL,
	`timestamp`               datetime                             NOT NULL,
	`anrede`                  varchar(16) COLLATE utf8_unicode_ci           DEFAULT NULL,
	`firma`                   varchar(100) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`titel`                   varchar(16) COLLATE utf8_unicode_ci           DEFAULT NULL,
	`name`                    varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`vorname`                 varchar(50) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
	`nachname`                varchar(50) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
	`strasse`                 varchar(100) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`plz`                     varchar(8) COLLATE utf8_unicode_ci            DEFAULT NULL,
	`ort`                     varchar(100) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`country`                 varchar(8) COLLATE utf8_unicode_ci            DEFAULT '',
	`email`                   varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`phone`                   varchar(30) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
	`dob`                     date                                          DEFAULT NULL,
	`wikimedium_shipping`     varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`membership_type`         varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sustaining',
	`payment_type`            varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'BEZ',
	`membership_fee`          int(11) NOT NULL DEFAULT '0',
	`membership_fee_interval` smallint(6) DEFAULT '12',
	`account_number`          varchar(16) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
	`bank_name`               varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`bank_code`               varchar(16) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
	`iban`                    varchar(32) COLLATE utf8_unicode_ci           DEFAULT '',
	`bic`                     varchar(32) COLLATE utf8_unicode_ci           DEFAULT '',
	`account_holder`          varchar(50) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
	`comment`                 longtext COLLATE utf8_unicode_ci     NOT NULL,
	`export`                  datetime                                      DEFAULT NULL,
	`backup`                  datetime                                      DEFAULT NULL,
	`wikilogin`               tinyint(1) NOT NULL DEFAULT '0',
	`tracking`                varchar(50) COLLATE utf8_unicode_ci           DEFAULT NULL,
	`data`                    longtext COLLATE utf8_unicode_ci,
	`donation_receipt`        tinyint(1) DEFAULT NULL,
	PRIMARY KEY (`id`),
	FULLTEXT KEY `m_email` (`email`),
	FULLTEXT KEY `m_name` (`name`),
	FULLTEXT KEY `m_ort` (`ort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `membership_incentive`
(
	`membership_id` int(11) NOT NULL,
	`incentive_id`  int(11) NOT NULL,
	PRIMARY KEY (`membership_id`, `incentive_id`),
	KEY             `IDX_4AE7CF6F1FB354CD` (`membership_id`),
	KEY             `IDX_4AE7CF6FF17F400F` (`incentive_id`),
	CONSTRAINT `FK_4AE7CF6F1FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `request` (`id`),
	CONSTRAINT `FK_4AE7CF6FF17F400F` FOREIGN KEY (`incentive_id`) REFERENCES `incentive` (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `spenden`
(
	`id`            int(11) NOT NULL AUTO_INCREMENT,
	`payment_id`    int(11) DEFAULT NULL,
	`status`        char(1) COLLATE utf8_unicode_ci      NOT NULL DEFAULT 'N',
	`name`          varchar(250) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`ort`           varchar(250) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`email`         varchar(250) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`info`          tinyint(1) NOT NULL DEFAULT '0',
	`bescheinigung` tinyint(1) DEFAULT NULL,
	`eintrag`       varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`betrag`        varchar(250) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`periode`       smallint(6) NOT NULL DEFAULT '0',
	`zahlweise`     char(3) COLLATE utf8_unicode_ci      NOT NULL DEFAULT 'BEZ',
	`kommentar`     longtext COLLATE utf8_unicode_ci     NOT NULL,
	`ueb_code`      varchar(32) COLLATE utf8_unicode_ci  NOT NULL DEFAULT '',
	`data`          longtext COLLATE utf8_unicode_ci,
	`source`        varchar(250) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`remote_addr`   varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`hash`          varchar(250) COLLATE utf8_unicode_ci          DEFAULT NULL,
	`is_public`     tinyint(1) NOT NULL DEFAULT '0',
	`dt_new`        datetime                             NOT NULL,
	`dt_del`        datetime                                      DEFAULT NULL,
	`dt_exp`        datetime                                      DEFAULT NULL,
	`dt_gruen`      datetime                                      DEFAULT NULL,
	`dt_backup`     datetime                                      DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `UNIQ_3CBBD0454C3A3BB` (`payment_id`),
	KEY             `d_dt_new` (`dt_new`,`is_public`),
	KEY             `d_zahlweise` (`zahlweise`,`dt_new`),
	KEY             `d_dt_gruen` (`dt_gruen`,`dt_del`),
	KEY             `d_ueb_code` (`ueb_code`),
	KEY             `d_dt_backup` (`dt_backup`),
	KEY             `d_status` (`status`,`dt_new`),
	KEY             `d_comment_list` (`is_public`,`dt_del`),
	FULLTEXT KEY `d_email` (`email`),
	FULLTEXT KEY `d_name` (`name`),
	FULLTEXT KEY `d_ort` (`ort`),
	CONSTRAINT `FK_3CBBD0454C3A3BB` FOREIGN KEY (`payment_id`) REFERENCES `donation_payment` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscription`
(
	`id`               int(11) NOT NULL AUTO_INCREMENT,
	`email`            varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`export`           datetime                                      DEFAULT NULL,
	`backup`           datetime                                      DEFAULT NULL,
	`status`           smallint(6) DEFAULT NULL,
	`confirmationCode` varchar(32) COLLATE utf8_unicode_ci           DEFAULT NULL,
	`tracking`         varchar(50) COLLATE utf8_unicode_ci           DEFAULT NULL,
	`source`           varchar(50) COLLATE utf8_unicode_ci           DEFAULT NULL,
	`createdAt`        datetime                                      DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
