<template>
	<div class="error-wrapper has-padding-18">
		<h3>{{ $t( 'errors_summary_title' ) }}</h3>
		<span v-for="field in invalidFields" :key="field">
			<a :href="field">{{ $t( field.label ) }}</a> ↓ - {{ $t( field.errorMessage ) }}<br>
		</span>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { ErrorSummaryData } from '@/view_models/ErrorSummaryData';

export default Vue.extend( {
	name: 'ErrorSummary',
	props: {
		invalidFields: Object as () => ErrorSummaryData,
	},
} );
</script>
