<template>
	<b-field :type="type" :label="$t( label )" class="input-wrapper">
		<input type="text"
				ref="input"
				:id="id"
				:name="id"
				:placeholder="$t( placeholder )"
				v-model="value"
				@blur="$emit( event, eventValue )">
	</b-field>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'InputWrapper',
	props: {
		id: String,
		placeholder: String,
		value: String,
		event: String,
		eventValue: String,
		type: Object,
		label: String,
	},
	mounted: function () {
		( this.$refs.input as any ).addEventListener( 'animationstart', ( e: any ) => {
			console.log( 'animation name', e.animationName );
			switch ( e.animationName ) {
				case 'onAutoFillStart':
					this.onAutoFillStart();
					break;
				case 'onAutoFillCancel':
					this.onAutoFillCancel();
					break;
			}
		}, false );
	},
	methods: {
		onAutoFillStart: function () {
			console.log( 'autofill on autofill start' );
			this.$emit( 'input', ( this.$refs.input as any ).value );
			this.$emit( this.$props.event, this.$props.eventValue );
		},
		onAutoFillCancel: function () {
			return;
		},
	},
} );
</script>

<style lang="scss">
	@import "@/scss/custom.scss";
</style>
