const path = require( 'path' );
const { getIfUtils, removeEmpty } = require( 'webpack-config-utils' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CleanWebpackPlugin = require( 'clean-webpack-plugin' );
const ConcatPlugin = require( 'webpack-concat-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );

// TODO
// - Copy fonts, pdfs etc
// - minify images (and make font regex matcher more specific to put the logo SVGs into images folder)
// - build faq (+ vue, with extra CSS file)
// - replace npm build scripts
// - delete gulpfile & browserify
// - Add dev server & integrate HMR into rest of application

const OUTPUT_PATH = path.resolve( __dirname, 'web' );

module.exports = mode => {
	const { ifProduction } = getIfUtils( mode );

	return 	{
		mode: ifProduction( 'production', 'development' ),
		entry: {
			'scripts/wmde': './src/app/main.js',
			'css/main': './src/sass/main.scss'
		},
		output: {
			filename: '[name].js',
			path: OUTPUT_PATH,
			library: 'WMDE'
		},
		plugins: [
			new CleanWebpackPlugin( OUTPUT_PATH, { exclude:  ['.gitkeep'], verbose: true } ),
			new MiniCssExtractPlugin({
				filename: "[name].css"
			}),
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
				{ from: 'src/scripts/*.js', to: 'scripts/', flatten: true  } // TODO transform with uglify
			] )
		],
		module: {
			rules: [
				{
					test: /\.scss$/,
					use: [
						{
							// Extract CSS modules into files
							loader: MiniCssExtractPlugin.loader,
							options: {
								publicPath: '../'
							}
						},
						{ loader:  "css-loader" }, // translates CSS into CommonJS
						{
							// Loader for webpack to process CSS with PostCSS
							loader: 'postcss-loader',
							options: {
								plugins: function () {
									return removeEmpty( [
										require( 'autoprefixer' ),
										ifProduction(
											require( 'postcss-clean' )( {
												compatibility: 'ie10'
											} )
										)
									] );
								}
							}
						},
						{
							loader: "sass-loader",
							options: {
								outputStyle: 'nested', // libsass doesn't support expanded yet
								precision: 10,
								includePaths: [ './node_modules' ],
								sourceMap: true,
								sourceMapContents: false
							}
						} // compiles Sass to CSS, using Node Sass by default
					]
				},
				{
					test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
					use: [{
						loader: 'file-loader',
						options: {
							name: '[name].[ext]',
							outputPath: 'assets/fonts/'
						}
					}]
				}
			]
		},
		devtool: ifProduction( 'source-map', 'eval' )
	}
};