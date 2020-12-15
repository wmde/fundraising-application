import { mount, createLocalVue } from '@vue/test-utils';
import Incentive from '@/components/pages/membership_form/Incentive.vue';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );

describe( 'Incentive', () => {

	it( 'incentive checkbox is checked when set on initial render', () => {
		const wrapper = mount( Incentive, {
				localVue,
				propsData: {
					message: '',
					defaultChecked: true,
					value: 'Playstation 5',
				},
			} ),
			checkBox = wrapper.find( '#incentive' );

		expect( checkBox.props().value ).toBe( true );
	} );

	it( 'incentive checkbox is not checked when not set on initial render', () => {
		const wrapper = mount( Incentive, {
				localVue,
				propsData: {
					message: '',
					defaultChecked: false,
					value: 'Playstation 5',
				},
			} ),
			checkBox = wrapper.find( '#incentive' );

		expect( checkBox.props().value ).toBe( false );
	} );

	it( 'emits toggle event on mount', () => {
		const wrapper = mount( Incentive, {
				localVue,
				propsData: {
					message: '',
					defaultChecked: false,
					value: 'Playstation 5',
				},
			} ),
			event = 'incentive-toggled';
		expect( wrapper.emitted( event )![ 0 ] ).not.toBeUndefined();
	} );

	it( 'emits toggle event on change', () => {
		const wrapper = mount( Incentive, {
				localVue,
				propsData: {
					message: '',
					defaultChecked: false,
					value: 'Playstation 5',
				},
			} ),
			event = 'incentive-toggled',
			checkBox = wrapper.find( '#incentive' );
		checkBox.trigger( 'click' );
		expect( wrapper.emitted( event )![ 1 ] ).not.toBeUndefined();
	} );

	it( 'only sends incentive tag when checked', () => {
		const wrapper = mount( Incentive, {
				localVue,
				propsData: {
					message: '',
					defaultChecked: true,
					value: 'Playstation 5',
				},
			} ),
			event = 'incentive-toggled',
			checkBox = wrapper.find( '#incentive' );

		checkBox.trigger( 'click' );
		expect( wrapper.emitted( event )![ 1 ] ).toEqual( [ { checked: false, incentive: '' } ] );

		checkBox.trigger( 'click' );
		expect( wrapper.emitted( event )![ 2 ] ).toEqual( [ { checked: true, incentive: 'Playstation 5' } ] );
	} );

} );
