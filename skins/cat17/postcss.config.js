module.exports = {
	plugins: [
		require( 'autoprefixer' )( {
			"browsers": [
				'last 2 versions',
				'android 4',
				'opera 12',
				'iOS >= 7'
			]
		} ),
		require( 'postcss-clean' )( {
			compatibility: 'ie8'
		} )
	]
}
