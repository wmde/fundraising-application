<?php
declare( strict_types=1 );

return [
	'table_storage' => [
		'table_name' => 'doctrine_migration_versions',
		'version_column_name' => 'version',
		'version_column_length' => 1024,
		'executed_at_column_name' => 'executed_at',
		'execution_time_column_name' => 'execution_time',
	],

	'migrations_paths' => [
		'WMDE\Fundraising\DonationContext\DataAccess\Migrations' => './vendor/wmde/fundraising-donations/src/DataAccess/Migrations',
		'WMDE\Fundraising\MembershipContext\DataAccess\Migrations' => './vendor/wmde/fundraising-memberships/src/DataAccess/Migrations',
		'WMDE\Fundraising\PaymentContext\DataAccess\Migrations' => './vendor/wmde/fundraising-payments/src/DataAccess/Migrations',
	],

	'all_or_nothing' => true,
	'transactional' => true,
	'check_database_platform' => true,
	'organize_migrations' => 'none',
	'connection' => null,
	'em' => null,
];
