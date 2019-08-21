import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import Supporters from '@/components/pages/Supporters.vue';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'Supporters.vue', () => {
	it( 'reacts to emitted supporter-opened event by setting the visible supporter ID', () => {
		const wrapper = mount( Supporters, {
			localVue,
			propsData: {
				supporters: [
					{
						name: 'Test',
						amount: '1234,00 €',
						comment: 'Blah',
					},
				],
			},
			mocks: {
				$t: ( key: string ) => key,
			},
		} );
		( wrapper.find( '.accordion-item' ) as any ).vm.$emit( 'supporter-opened', 1 );
		expect( wrapper.vm.$data.visibleSupporterId ).toBe( 1 );
	} );

	it( 'reacts to emitted supporter-closed event by setting the visible supporter ID to null', () => {
		const wrapper = mount( Supporters, {
			localVue,
			propsData: {
				supporters: [
					{
						name: 'Test',
						amount: '1234,00 €',
						comment: 'Blah',
					},
				],
			},
			mocks: {
				$t: ( key: string ) => key,
			},
		} );
		wrapper.setData( { visibleSupporterId: 123 } );
		( wrapper.find( '.accordion-item' ) as any ).vm.$emit( 'supporter-closed' );
		expect( wrapper.vm.$data.visibleSupporterId ).toBeNull();
	} );
} );
