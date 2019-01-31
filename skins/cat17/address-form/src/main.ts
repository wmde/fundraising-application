import Vue from "vue";
import store from "./store";
import AddressForm from "./AddressForm.vue";

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
				isCompany: JSON.parse( addressElement.getAttribute('data-is-company') ),
				messages: JSON.parse(addressElement.getAttribute('data-messages')),
                validateAddressURL: initAddressForm.getAttribute('data-validate-address-url')
                updateAddressURL: initAddressForm.getAttribute('data-update-address-url')
			}
		}
	)
} ).$mount( "#updateAddress" );
