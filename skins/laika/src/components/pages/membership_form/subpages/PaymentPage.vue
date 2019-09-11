<template>
	<div class="payment-page">
		<payment v-bind="$props"></payment>
		<div class="summary-wrapper has-margin-top-18 has-outside-border">
			<membership-summary :membership-application="membershipApplication" :address="addressSummary"></membership-summary>
			<submit-values :tracking-data="{}"></submit-values>
			<div class="columns has-margin-top-18">
				<div class="column">
					<b-button id="previous-btn" class="level-item"
						@click="$emit( 'previous-page' )"
						type="is-primary is-main"
						outlined>
						{{ $t('membership_form_section_back') }}
					</b-button>
				</div>
				<div class="column">
					<b-button id="submit-btn" :class="[ $store.getters.isValidating ? 'is-loading' : '', 'level-item']"
						@click="submit"
						type="is-primary is-main">
						{{ $t('membership_form_finalize') }}
					</b-button>
				</div>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import Payment from '@/components/pages/membership_form/Payment.vue';
import SubmitValues from '@/components/pages/membership_form/SubmitValues.vue';
import MembershipSummary from '@/components/MembershipSummary.vue';
import { NS_BANKDATA, NS_MEMBERSHIP_ADDRESS, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import { action } from '@/store/util';
import { markEmptyValuesAsInvalid as markEmptyFeeValuesAsInvalid } from '@/store/membership_fee/actionTypes';
import { markEmptyValuesAsInvalid as markemptyBankDataValuesAsInvalid } from '@/store/bankdata/actionTypes';
import { waitForServerValidationToFinish } from '@/wait_for_server_validation';
import { membershipTypeName } from '@/view_models/MembershipTypeModel';
import { addressTypeName } from '@/view_models/AddressTypeModel';

export default Vue.extend( {
	name: 'PaymentPage',
	components: {
		Payment,
		SubmitValues,
		MembershipSummary,
	},
	props: {
		validateFeeUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Number>,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
	},
	methods: {
		submit() {
			waitForServerValidationToFinish( this.$store ).then( () => {
				return Promise.all( [
					this.$store.dispatch( action( NS_MEMBERSHIP_FEE, markEmptyFeeValuesAsInvalid ) ),
					this.$store.dispatch( action( NS_BANKDATA, markemptyBankDataValuesAsInvalid ) ),
				] ).then( () => {
					if ( this.$store.getters[ NS_MEMBERSHIP_FEE + '/paymentDataIsValid' ] &&
						this.$store.getters[ NS_BANKDATA + '/bankDataIsValid' ] ) {
						this.$emit( 'submit-membership' );
					} else {
						document.getElementsByClassName( 'is-danger' )[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center', inline: 'nearest' } );
					}
				} );
			} );

		},
	},
	computed: {
		membershipApplication: {
			get(): object {
				const payment = this.$store.state[ NS_MEMBERSHIP_FEE ].values;
				return {
					paymentIntervalInMonths: payment.interval,
					membershipFee: payment.fee / 100,
					paymentType: payment.type,
					membershipType: membershipTypeName( this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/membershipType' ] ),
				};
			},
		},
		addressSummary: {
			get(): object {
				return {
					...this.$store.state[ NS_MEMBERSHIP_ADDRESS ].values,
					fullName: this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/fullName' ],
					streetAddress: this.$store.state[ NS_MEMBERSHIP_ADDRESS ].values.street,
					postalCode: this.$store.state[ NS_MEMBERSHIP_ADDRESS ].values.postcode,
					countryCode: this.$store.state[ NS_MEMBERSHIP_ADDRESS ].values.country,
					applicantType: addressTypeName( this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/addressType' ] ),
				};
			},
		},
	},
} );
</script>
