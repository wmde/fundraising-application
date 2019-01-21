import Vue from "vue";
import AddressForm from "./AddressForm.vue";
import store from "./store";

Vue.config.productionTip = false;
let addressElement: any = document.getElementById( 'updateAddress' );
let initAddressForm: any = document.getElementById( 'address-form' );
new Vue( {
	store,
	render: h => h(
		AddressForm,
		{
			props: {
				addressToken: addressElement.getAttribute('data-address-token'),
				isCompany: addressElement.getAttribute('data-is-company'),
				messages: JSON.parse(addressElement.getAttribute('data-messages')),
				initAddressForm: {
					validateAddressURL: initAddressForm.getAttribute('data-validate-address-url'),
				}
			}
		}
	)
} ).$mount( "#updateAddress" );
