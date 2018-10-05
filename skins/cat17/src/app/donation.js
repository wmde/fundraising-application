// This is the new donation form entry point.
// Currently, only parts of the donation for are rendered as vue components
// As we do more and more with vue components, this file will grow and resemble a regular entry point

import Vue from 'vue';
import { reduxStorePlugin, connect } from 'redux-vue';
import BankData from './components/BankData.vue'
import actions from './lib/actions';

(function () {

	// TODO move mapper functions to indivdual "wrapped" components

	function mapStateToProps( state ) {
		return {
			formContent: state.donationFormContent
		}
	}

	function mapActionToProps( dispatch ) {
		return {
			// TODO create appropriate actions, if needed
			justAnExample() {
				dispatch( actions.newChangeContentAction( 'firstName', 'Otto' ) )
			}

		}
	}

	window.addEventListener( 'load', function (evt) {
		/** global: WMDE */

		Vue.use(reduxStorePlugin);

		new Vue({
			// FIXME Import and create store directly when we no longer use the global variable anywhere else
			store: WMDE.donationStore,
			render: (h) => h( connect( mapStateToProps, mapActionToProps )( BankData ) )
		}).$mount('#bank-name');
	});

}());
