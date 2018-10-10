// The following line loads the standalone build of Vue instead of the runtime-only build,
// so you don't have to do: import Vue from 'vue/dist/vue'
// This is done with the browser options. For the config, see package.json
var Vue = require( 'vue' );
var Faq = require( './Faq.vue' );

var faqDataElement = document.getElementById( 'faq' );

new Vue({ // eslint-disable-line no-new
	el: '#faq',
	render: ( h ) => h(
		Faq,
	    {
			props: {
				messages: JSON.parse( faqDataElement.getAttribute( 'data-messages' ) ),
				content: JSON.parse( faqDataElement.getAttribute( 'data-content' ) )
			}
		}
	)
} );
