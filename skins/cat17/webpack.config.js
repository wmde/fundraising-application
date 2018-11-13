const webpack = require('webpack');
const path = require( 'path' );
const { getIfUtils } = require( 'webpack-config-utils' );
const ConcatPlugin = require( 'webpack-concat-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const VueLoaderPlugin = require('vue-loader/lib/plugin')

// TODO
// - build faq (+ vue, with extra CSS file)
// - Add watch task to npm

module.exports = mode => {
	const { ifProduction } = getIfUtils( mode );
	const OUTPUT_PATH = ifProduction(
		path.resolve( __dirname, 'web' ),
		path.resolve( __dirname, '../../web/skins/cat17' )
	);

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
				{ from: 'assets/fonts/**/*', context: 'src'  },
				{ from: 'assets/images/*', context: 'src' }
			], { debug: false } ),
			new VueLoaderPlugin(),
			new webpack.ProvidePlugin( {
				Promise: ['es6-promise', 'Promise']
			} )
		],
		module: {
			rules: [
				{
					test: /\.vue$/,
					loader: 'vue-loader'
				},
				{
					test: /\.(js|jsx|mjs)$/,
					loader: 'babel-loader',
					options: {
						babelrc: true
					},
					exclude: /node_modules/
				}
			]
		},
		devtool: ifProduction( 'source-map', 'eval' ),
		stats: ifProduction(
			{
				all: false,
				modules: true,
				maxModules: 0,
				errors: true,
				warnings: true,
				warningsFilter: [/font/]
			},
			'normal'
		),
		resolve: {
			alias: {
				'vue$': 'vue/dist/vue.esm.js'
			}
		},
		externals: {
			jquery: 'jQuery'
		}
	}
};