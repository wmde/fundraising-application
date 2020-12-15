<template>
	<div class="incentive-box">
		<b-checkbox
			type="checkbox"
			class="is-inline-checkbox"
			id="incentive"
			name="incentive"
			:value="value"
			v-model="checked"
			@change.native="onChange()">
			{{ message }}
		</b-checkbox>
	</div>

</template>

<script>
import Vue from 'vue';

export default Vue.extend( {
	name: 'Incentive',
	data: function () {
		return {
			checked: this.$props.defaultChecked,
		};
	},
	props: {
		message: String,
		defaultChecked: Boolean,
		value: String,
	},
	methods: {
		onChange: function () {
			this.$emit( 'incentive-toggled', {
				checked: this.checked,
				incentive: this.checked ? this.value : '',
			} );
		},
	},
	mounted() {
		this.onChange();
	},
} );
</script>
