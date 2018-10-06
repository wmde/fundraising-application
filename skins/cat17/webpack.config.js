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
			'scripts/wmde': './src/app/main.js',
			'styles/main': './src/sass/main.scss'
		},
		output: {
			filename: '[name].js',
			path: path.resolve( __dirname, 'web' )
		},
		module: {
			rules: [
				{
					test: /\.scss$/,
					use: [
						{ loader: "style-loader" },
						{ loader:  "css-loader" }, // translates CSS into CommonJS
						{
							// Loader for webpack to process CSS with PostCSS
							loader: 'postcss-loader',
							options: {
								plugins: function () {
									return [
										require('autoprefixer')
									];
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