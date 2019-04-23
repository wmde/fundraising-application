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
		access_denied: 'src/pages/access_denied.ts',
		address_update_success: 'src/pages/address_update_success.ts',
		comment_list: 'src/pages/comment_list.ts',
		contact_form: 'src/pages/contact_form.ts',
		donation_cancellation_confirmation: 'src/pages/donation_cancellation_confirmation.ts',
		donation_comments: 'src/pages/donation_comment.ts',
		donation_confirmation: 'src/pages/donation_confirmation.ts',
		donation_form: 'src/pages/donation_form.ts',
		error: 'src/pages/error.ts',
		frequent_questions: 'src/pages/frequent_questions.ts',
		funds_usage: 'src/pages/funds_usage.ts',
		membership_application: 'src/pages/membership_application.ts',
		membership_application_cancellation_confirmation: 'src/pages/membership_application_cancellation_confirmation.ts',
		membership_application_confirmation: 'src/pages/membership_application_cancellation_confirmation.ts',
		page_not_found: 'src/pages/page_not_found.ts',
		subscription_confirmation: 'src/pages/subscription_confirmation.ts',
		system_message: 'src/pages/system_message.ts',
		update_address: 'src/pages/update_address.ts',
		warning_page: 'src/pages/warning_page.ts',
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
