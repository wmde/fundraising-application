import Vue from 'vue'

import UpdateDonorForm from './components/update-donor-form';


const app = new Vue({
	/*
	el:'#form-donation',
	render: ( h ) => {
		return h( 'field-wrapper', { name:'test'}, 'wrapped text')
	},
	*/
	components: {
		UpdateDonorForm
	},
	template: '<update-donor-form />'

});

app.$mount('#form-donation')