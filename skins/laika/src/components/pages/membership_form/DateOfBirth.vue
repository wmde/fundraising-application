<template>
	<fieldset>
		<label for="birthDate" class="subtitle has-margin-top-36">{{ $t( 'membership_form_birth_date_label' ) }}</label>
		<b-field :type="{ 'is-danger': dateHasError }">
			<b-input type="text"
				id="birthDate"
				:placeholder="$t( 'membership_form_birth_date_placeholder' )"
				v-model="date"
				@blur="validateDate">
			</b-input>
		</b-field>
		<span v-if="dateHasError" class="help is-danger">{{ $t( 'membership_form_birth_date_error' ) }}</span>
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
			datePattern: new RegExp( this.$props.validationPattern ),
			dateHasError: false,
		};
	},
	props: {
		validationPattern: String,
	},
	mounted() {
		this.$data.date = this.$store.state[ NS_MEMBERSHIP_ADDRESS ].values.date;
		if ( this.$data.date !== '' ) {
			this.validateDate();
		}
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
			if ( this.$data.date === '' ) {
				return true;
			}
			return this.datePattern.test( this.$data.date );
		},
	},
} );
</script>
