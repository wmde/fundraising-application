import Vue from 'vue'
import VueI18n from 'vue-i18n'

import UpdateDonorForm from './components/update-donor-form';

// It's ok for now to use the dev dependency, but in prod we need to be able to auto-update
// In the future, we might split message files to get smaller sizes
import messages from '../../../vendor/wmde/fundraising-frontend-content/i18n/de_DE/messages/messages.json';

Vue.use(VueI18n);


const app = new Vue({
	components: {
		UpdateDonorForm
	},
	template: '<update-donor-form />',
	i18n: {
		locale: 'de',
		messages: {
			de: messages
		}
	}

});

app.$mount('#form-donation')