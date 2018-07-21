import Vue from 'vue'

import FieldWrapper from './components/field-wrapper';



const app = new Vue({
	/*
	el:'#form-donation',
	render: ( h ) => {
		return h( 'field-wrapper', { name:'test'}, 'wrapped text')
	},
	*/
	components: {
		FieldWrapper
	},
	template: '<field-wrapper name="ergo" label="foo" errorMessage="I am wrong">test</field-wrapper>'

});

app.$mount('#form-donation')