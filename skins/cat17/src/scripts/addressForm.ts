import Vue from 'vue';
import AddressForm from '../pages/AddressForm.vue';
let addressDataElement: any = document.getElementById( 'updateAddress' );

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
