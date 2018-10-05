const path = require( 'path' );
const merge = require( 'webpack-merge' )

// TODO
// - Generate styles
// - Concatenate script assets
// - Copy fonts, pdfs etc
// - use environment configurations (dev, prod)
// - minify images
// - build faq (+ vue)
// - replace npm build scripts
// - delete gulpfile & browserify
// - Add dev server & integrate HMR into rest of application

const commonConfig = merge( [
	{
		entry: {
			'scripts/wmde': './src/app/main.js'
		},
		output: {
			filename: '[name].js',
			path: path.resolve( __dirname, 'web' )
		}
	}
] );

const productionConfig = merge( [
	{
		devtool: 'source-map'
	}
] );

const developmentConfig = merge( [
	{
		devtool: 'eval'
	}
] );

module.exports = mode => {
	mode = mode || 'development';

	if (mode === "production") {
		return merge( commonConfig, productionConfig, { mode } );
	}

	return merge( commonConfig, developmentConfig, { mode } );
};