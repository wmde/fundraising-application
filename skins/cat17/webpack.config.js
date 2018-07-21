const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const PATHS = {
	app: path.join(__dirname, 'src/app'),
	build: path.resolve(__dirname, '../../web/skins/cat17/scripts'),
	publicPath: '/skins/cat17/scripts' // absolute path from webroot
};

module.exports = {
  entry: './src/updateDonation.js',
  output: {
    filename: 'donorUpdateVue.js',
    path: PATHS.build
  },
    module: {
      rules: [
		  {
			  test: /\.vue$/,
			  loader: 'vue-loader',
		  },
		  {
			  test: /\.js$/,
			  loader: 'babel-loader',
			  include: [PATHS.app]
		  },
		  {
			  test: /\.css$/,
			  loader: 'css-loader',
		  },
     ]
  },
	resolve: {
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		},
		extensions: ['*', '.js', '.vue', '.json']
	},
	plugins: [
		new VueLoaderPlugin()
	],
    mode: 'development'
};
