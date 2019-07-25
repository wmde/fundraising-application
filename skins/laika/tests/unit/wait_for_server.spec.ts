import { ValidationState, waitForServerValidationToFinish } from '@/wait_for_server_validation';
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use( Vuex );

describe( 'waitForServerValidationToFinish', () => {
	it( 'instantly returns when not validating', () => {
		const store = new Vuex.Store( {
			getters: {
				isValidating: () => false,
			},
		} );
		return waitForServerValidationToFinish( store ).then( validationState => {
			expect( validationState ).toBe( ValidationState.IMMEDIATE );
		} );
	} );

	it( 'delays resolution until validation finishes', () => {
		const store = new Vuex.Store( {
			state: {
				validationInProgress: true,
			},
			getters: {
				isValidating: state => state.validationInProgress,
			},
			mutations: {
				endValidation: state => { state.validationInProgress = false; },
			},
		} );
		const validationPromise = waitForServerValidationToFinish( store ).then( validationState => {
			expect( validationState ).toBe( ValidationState.WAS_VALIDATING );
		} );
		store.commit( 'endValidation' );
		return validationPromise;
	} );
} );
