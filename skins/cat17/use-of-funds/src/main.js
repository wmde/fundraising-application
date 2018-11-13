// The following line loads the standalone build of Vue instead of the runtime-only build,
// so you don't have to do: import Vue from 'vue/dist/vue'
// This is done with the browser options. For the config, see package.json
var Vue = require( 'vue' ),
	UseOfFunds = require( './UseOfFunds.vue' ),
	fundsDataElement = document.getElementById( 'funds' );

new Vue( { // eslint-disable-line no-new
	el: '#funds',
	render: h => h(
		UseOfFunds,
		{
			props: {
				messages: JSON.parse( fundsDataElement.getAttribute( 'data-messages' ) ),
				content: JSON.parse( fundsDataElement.getAttribute( 'data-content' ) )
			}
		}
	)
} );
