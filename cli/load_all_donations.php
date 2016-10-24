<?php
// This is a throwaway script that loads all donations as domain objects to see if the loading fails.
// This should be thrown away when all donations can be loaded without a hitch.
declare( strict_types = 1 );

require_once __DIR__ . '/../vendor/autoload.php';

use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\ConfigReader;

/**
 * @var FunFunFactory $ffFactory
 */
$ffFactory = call_user_func( function() {
	$prodConfigPath = __DIR__ . '/../app/config/config.prod.json';

	$configReader = new ConfigReader(
		new \FileFetcher\SimpleFileFetcher(),
		__DIR__ . '/../app/config/config.dist.json',
		is_readable( $prodConfigPath ) ? $prodConfigPath : null
	);

	return new FunFunFactory( $configReader->getConfig() );
} );

function shutdown() {
	global $id;

	$error = error_get_last();

	if ( $error !== null ) {
		printf( "\nError while loading ID %d\n", $id );
	} else {
		echo "OK!\n";
	}
}

register_shutdown_function( 'shutdown' );
gc_enable();
$repo = $ffFactory->getDonationRepository();
$conn = $ffFactory->getEntityManager()->getConnection();
$conn->getConfiguration()->setSQLLogger( null );
$res1 = $conn->query( 'SELECT COUNT(id) FROM spenden ' );
$numDonations = $res1->fetchColumn();
$tick = round( $numDonations / 100 );
$res2 = $conn->query( 'SELECT id FROM spenden' );
printf( "%d donations found.\n", $numDonations );
$cnt = 0;
$started = time();
while ( $id = $res2->fetchColumn() ) {
		if ( !( $cnt % 200 ) || $cnt == $numDonations - 1 ) {
			$percentComplete = (int) round( $cnt / $tick );
			$elapsed = time() - $started;
			$donationsPerSecond = $cnt / max( $elapsed, 1 );
			$eta = (int) round( ( $numDonations - $cnt ) / max( $donationsPerSecond, 1 ) );
			printf( "%100s %2d%%, ID %8d, ETA %-20s\r", str_repeat( '#', $percentComplete ), $percentComplete, $id, date( 'i:s', $eta ) );
		}
	try {
		$donation = $repo->getDonationById( (int) $id );
		unset( $donation );
		if ( !( $cnt % 1500 ) ) {
			gc_collect_cycles();
		}
	} catch ( Exception $e ) {
		printf( "\nError while loading ID %d\n", $id );
		throw $e;
	}

	$cnt++;
}
echo "\n";