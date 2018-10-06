const path = require( 'path' );
const glob = require( 'glob' );
const { getIfUtils } = require( 'webpack-config-utils' );
const CleanWebpackPlugin = require( 'clean-webpack-plugin' );
const ConcatPlugin = require( 'webpack-concat-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const ImageminPlugin = require( 'imagemin-webpack-plugin' ).default;

// TODO
// - build faq (+ vue, with extra CSS file)
// - delete gulpfile & browserify
// - Add dev server & integrate HMR into rest of application

const OUTPUT_PATH = path.resolve( __dirname, 'web' );

module.exports = mode => {
	const { ifProduction } = getIfUtils( mode );

	return 	{
		mode: ifProduction( 'production', 'development' ),
		entry: {
			'scripts/wmde': './src/app/main.js'
		},
		output: {
			filename: '[name].js',
			path: OUTPUT_PATH,
			library: 'WMDE'
		},
		plugins: [
			new CleanWebpackPlugin( OUTPUT_PATH, {
				exclude:  [ '.gitkeep', 'css' ],
				verbose: true
			} ),
			new ConcatPlugin( {
				uglify: true,
				sourceMap: true,
				name: 'vendor',
				outputPath: 'scripts',
				fileName: 'vendor.js',
				filesToConcat: [
					'jquery/dist/jquery.js',
					'jcf/dist/js/jcf.js',
					'jcf/dist/js/jcf.select.js',
					'jcf/dist/js/jcf.scrollable.js'
				],
				attributes: {
					async: true
				}
			} ),
			new CopyWebpackPlugin( [
				{ from: 'src/scripts/*.js', to: 'scripts/', flatten: true  }, // TODO transform with uglify
				{ from: 'assets/fonts/**/*', context: 'src'  }
			], { debug: false } ),
			new ImageminPlugin( {
				externalImages: {
					//context: 'src',
					sources: glob.sync('src/assets/images/*'),
					destination: 'web/assets/images',
					fileName: '[name].[ext]',
					jpegtran: { progressive: true },
					gifsicle: { interlaced: true },
					svgo: { plugins: [ { removeUnknownsAndDefaults: false }, { cleanupIDs: false } ] }
				}
			} )
		],
		devtool: ifProduction( 'source-map', 'eval' ),
		stats: {
			all: false,
			modules: true,
			maxModules: 0,
			errors: true,
			warnings: true,
			warningsFilter: [/font/]
		}
	}
};