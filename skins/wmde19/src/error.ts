import Vue from 'vue';
import Error from './Error.vue';

Vue.config.productionTip = false;

const dataElement = document.getElementById( 'app' );
let applicationVars: any = {}; // TODO Create interface
let messages = {};

if ( dataElement ) {
	applicationVars = JSON.parse( dataElement.dataset.applicationVars || '{}' );
	messages = JSON.parse( dataElement.dataset.applicationMessages || '{}' );
}

new Vue( {
	render: h => h( Error, {
		props: {
			errorMessage: applicationVars.message,
			messages: messages,
		},
	} ),
} ).$mount( '#app' );
