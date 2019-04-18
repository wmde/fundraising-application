const path = require( 'path' );

module.exports = {
	pages: {
		index: {
			// entry for the page
			entry: 'src/main.ts',
			// the source template
			template: 'public/index.html',
			// output as dist/index.html
			filename: 'index.html',
			// when using title option,
			// template title tag needs to be <title><%= htmlWebpackPlugin.options.title %></title>
			title: 'Index Page',
			// chunks to include on this page, by default includes
			// extracted common chunks and vendor chunks.
			chunks: [ 'chunk-vendors', 'chunk-common', 'index' ],
		},
		error: 'src/pages/error.ts',
	},
	devServer: {
		watchOptions: {
			poll: true,
		},
	},
	outputDir: path.resolve( __dirname, '../../web/skins/wmde19' ),

	chainWebpack: config => {
		if ( config.plugins.has( 'extract-css' ) ) {
			const extractCSSPlugin = config.plugin( 'extract-css' );
			if ( extractCSSPlugin ) {
				extractCSSPlugin.tap( () => [ {
					filename: 'css/[name].css',
					chunkFilename: 'css/[name].css',
				} ] );
			}
			// Preliminary solution for combining all CSS into one file
			// see https://github.com/webpack-contrib/mini-css-extract-plugin/issues/113
			config.optimization.splitChunks( {
				cacheGroups: {
					commons: {
						test: /node_modules/,
						chunks: 'initial',
						name: 'chunk-vendors',
					},
					'styles-compiled': {
						name: 'styles',
						test: module =>
							module.nameForCondition &&
							/\.(s?css|vue)$/.test( module.nameForCondition() ) && !/^javascript/.test( module.type ),
						chunks: 'all',
						enforce: true,
					},
				},
			} );
		}
		config.module
			.rule( 'vue' )
			.use( 'vue-loader' )
			.loader( 'vue-loader' )
			.tap( options => {
				options.compilerOptions.whitespace = 'condense';
				return options;
			} );
	},
	configureWebpack: {
		output: {
			filename: 'js/[name].js',
			chunkFilename: 'js/[name].js',
		},
	},
};
