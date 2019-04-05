import Vue from 'vue';
import Error from '@/components/pages/Error.vue';
import App from '@/components/App.vue';
import PageDataInitializer from '@/page_data_initializer';

Vue.config.productionTip = false;

interface ErrorModel {
	message: string
}

const pageData = new PageDataInitializer<ErrorModel>( '#app' );

new Vue( {
	render: h => h( App, {}, [ h( Error, {
		props: {
			errorMessage: pageData.applicationVars.message,
			messages: pageData.messages,
		},
	} ) ] ),
} ).$mount( '#app' );
