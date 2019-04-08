import Vue from 'vue';
import VueI18n from 'vue-i18n';
import Error from '@/components/pages/Error.vue';
import App from '@/components/App.vue';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';

Vue.config.productionTip = false;
Vue.use( VueI18n );

interface ErrorModel {
	message: string
}

const pageData = new PageDataInitializer<ErrorModel>( '#app' );

const i18n = new VueI18n( {
	locale: DEFAULT_LOCALE,
	messages: {
		[ DEFAULT_LOCALE ]: pageData.messages,
	},
} );

// TODO imagePath needs to be dynamically established
new Vue( {
	i18n,
	render: h => h( App, { props: { imagePath: 'http://localhost:7072' } }, [ h( Error, {
		props: {
			errorMessage: pageData.applicationVars.message,
		},
	} ) ] ),
} ).$mount( '#app' );
