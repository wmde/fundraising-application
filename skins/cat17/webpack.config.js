const path = require( 'path' );
const glob = require( 'glob' );
const { getIfUtils } = require( 'webpack-config-utils' );
const ConcatPlugin = require( 'webpack-concat-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const ImageminPlugin = require( 'imagemin-webpack-plugin' ).default;

// TODO
// - build faq (+ vue, with extra CSS file)
// - Add watch task to npm

const OUTPUT_PATH = path.resolve( __dirname, 'web' );

module.exports = mode => {
	const { ifProduction } = getIfUtils( mode );

	return {
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
			new ConcatPlugin( {
				uglify: ifProduction( true, false ),
				sourceMap: ifProduction( true, false ),
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
				cacheFolder: path.resolve('./.image-cache'),
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