import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import CompositionAPI from '@vue/composition-api';
import axios from 'axios';

import CookieNotice from '@/components/cookie_notice/CookieNotice.vue';

jest.mock( 'axios', () => ( {
	post: jest.fn( () => Promise.resolve( { 'status': 'OK' } ) ),
} ) );

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );
localVue.use( CompositionAPI );

describe( 'CookieNotice', () => {
	let wrapper: any;
	beforeEach( () => {
		wrapper = mount( CookieNotice, {
			localVue,
			mocks: {
				$t: ( key: string ) => key,
			},
		} );
		jest.resetModules();
		jest.clearAllMocks();
	} );

	it( 'changes view when check and back buttons are clicked', async () => {
		wrapper.find( '.check > button' ).trigger( 'click' );
		await wrapper.vm.$nextTick();
		expect( ( wrapper.vm as any ).showOptions ).toBeTruthy();

		wrapper.find( '.cookie-notice-back-button' ).trigger( 'click' );
		await wrapper.vm.$nextTick();
		expect( ( wrapper.vm as any ).showOptions ).toBeFalsy();
	} );

	it( 'posts consent when accept button is clicked', () => {
		const payload = new FormData();
		payload.append( 'cookie_consent', 'yes' );

		wrapper.find( '.accept > button' ).trigger( 'click' );

		expect( axios.post ).toHaveBeenCalledTimes( 1 );
		expect( axios.post ).toHaveBeenCalledWith(
			'http://localhost/set-cookie-preferences',
			payload,
			{ headers: { 'Content-Type': 'multipart/form-data' } }
		);
	} );

	it( 'posts consent when save button is clicked and consent given', async () => {
		const payload = new FormData();
		payload.append( 'cookie_consent', 'yes' );

		wrapper.find( '.check > button' ).trigger( 'click' );
		await wrapper.vm.$nextTick();
		wrapper.find( 'input[name=optional]' ).trigger( 'click' );
		wrapper.find( '.save > button' ).trigger( 'click' );

		expect( axios.post ).toHaveBeenCalledTimes( 1 );
		expect( axios.post ).toHaveBeenCalledWith(
			'http://localhost/set-cookie-preferences',
			payload,
			{ headers: { 'Content-Type': 'multipart/form-data' } }
		);
	} );

	it( 'does not post consent when save button is clicked and consent not given', async () => {
		const payload = new FormData();
		payload.append( 'cookie_consent', 'no' );

		wrapper.find( '.check > button' ).trigger( 'click' );
		await wrapper.vm.$nextTick();
		wrapper.find( '.save > button' ).trigger( 'click' );

		expect( axios.post ).toHaveBeenCalledTimes( 1 );
		expect( axios.post ).toHaveBeenCalledWith(
			'http://localhost/set-cookie-preferences',
			payload,
			{ headers: { 'Content-Type': 'multipart/form-data' } }
		);
	} );
} );
