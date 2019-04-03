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
		error: 'src/error.ts',
	},
};
