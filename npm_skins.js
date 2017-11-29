/**
 * Invoke an npm command in all skin folders
 */

const fs = require( 'fs' ),
	resolve = require( 'path' ).resolve,
	join = require( 'path' ).join,
	cp = require( 'child_process' ),
	skins = resolve( __dirname, './skins' ),
	commands = process.argv.slice( 2 ); // this script is invoked via `node npm_skins ...`, so lose the first two arguments

fs.readdirSync( skins ).forEach( function ( skin ) {
	var skinPath = join( skins, skin );

	// ensure path has package.json
	if ( !fs.existsSync( join( skinPath, 'package.json' ) ) ) {
		return;
	}

	cp.spawn( 'npm', commands, { env: process.env, cwd: skinPath, stdio: 'inherit' } )
		.on( 'close', function( code ) {
			if ( code !== 0 ) {
				process.exitCode = code;
			}
		} );
} );
