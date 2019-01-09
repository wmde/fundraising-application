// The following line loads the standalone build of Vue instead of the runtime-only build,
// so you don't have to do: import Vue from 'vue/dist/vue'
// This is done with the browser options. For the config, see package.json

var Vue = require( 'vue' ),
	AddressForm = require( '../pages/AddressForm.vue' ),
	addressDataElement = document.getElementById( 'updateAddress' );

new Vue( { // eslint-disable-line no-new
	el: '#updateAddress',
	render: h => h(
		AddressForm,
		{
			props: {
				addressToken: JSON.parse( addressDataElement.getAttribute( 'data-address-token' ) ),
				isCompany: JSON.parse( addressDataElement.getAttribute( 'data-is-company' ) )
			}
		}
	)
} );
