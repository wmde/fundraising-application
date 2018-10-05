const path = require( 'path' );

module.exports = {
	entry: {
		'scripts/wmde': './src/app/main.js'
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'web' )
	}
};