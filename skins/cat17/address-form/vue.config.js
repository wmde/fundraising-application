module.exports = {
	outputDir: "../../../web/skins/cat17/scripts/address-change",
	css: {
		extract: {
			filename: '[name].css'
		}
	},
    filenameHashing: false,
    // delete HTML related webpack plugins
    chainWebpack: config => {
        config.plugins.delete('html')
        config.plugins.delete('preload')
        config.plugins.delete('prefetch')
    }
}