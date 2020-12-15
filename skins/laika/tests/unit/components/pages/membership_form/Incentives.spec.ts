import { mount, createLocalVue } from '@vue/test-utils';
import Incentives from '@/components/pages/membership_form/Incentives.vue';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );

describe( 'Incentives', () => {

	it( 'incentive checkbox is checked when set on initial render', () => {
		const wrapper = mount( Incentives, {
				localVue,
				propsData: {
					message: '',
					incentiveChoices: [ 'tote_bag' ],
					defaultIncentives: [ 'tote_bag' ],
				},
			} ),
			checkBox = wrapper.find( '.incentive-tote_bag' );

		expect( checkBox.props().value ).toStrictEqual( [ 'tote_bag' ] );
	} );

	it( 'incentive checkbox is not checked when not set on initial render', () => {
		const wrapper = mount( Incentives, {
				localVue,
				propsData: {
					message: '',
					incentiveChoices: [ 'tote_bag' ],
					defaultIncentives: [],
				},
			} ),
			checkBox = wrapper.find( '.incentive-tote_bag' );

		expect( checkBox.props().value ).toStrictEqual( [] );
	} );

	it( 'emits toggle event on change', () => {
		const wrapper = mount( Incentives, {
				localVue,
				propsData: {
					message: '',
					incentiveChoices: [ 'tote_bag' ],
					defaultIncentives: [ 'tote_bag' ],
				},
			} ),
			event = 'incentives-changed',
			checkBox = wrapper.find( '.incentive-tote_bag' );

		checkBox.trigger( 'click' );
		expect( wrapper.emitted( event )![ 0 ] ).not.toBeUndefined();
	} );
} );
