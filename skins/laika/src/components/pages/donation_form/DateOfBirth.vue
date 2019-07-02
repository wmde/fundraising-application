<template>
    <fieldset>
		<label for="birthDate" class="subtitle has-margin-top-36">{{ $t( 'membership_birth_date_label' ) }}</label>
		<b-input type="text"
			id="birthDate"
			:placeholder="$t( 'membership_birth_date_placeholder' )"
			v-model="date"
			@blur="validateDate">
		</b-input>
		<span v-if="dateHasError" class="help is-danger">{{ $t( 'membership_birth_date_error' ) }}</span>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { action } from '@/store/util';
import { setDate } from '@/store/membership_address/actionTypes';

export default Vue.extend( {
	name: 'DateOfBirth',
	data: function () {
		return {
			date: '',
			datePattern: new RegExp( [
				'^(?:(?:31(\\.)',
				'(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\.)',
				'(?:0?[13-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\.)',
				'0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$',
				'|^(?:0?[1-9]|1\\d|2[0-8])(\\.)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$',
			].join( '' ) ),
			dateHasError: false,
		};
	},
	methods: {
		setDate: function () {
			this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setDate ), this.$data.date );
		},
		validateDate: function () {
			this.dateHasError = !this.dateIsValid();
			if ( !this.dateHasError ) {
				this.setDate();
			}
		},
		dateIsValid: function () {
			return this.datePattern.test( this.$data.date );
		},
	},
} );
</script>
