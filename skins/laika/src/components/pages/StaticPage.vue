<template>
    <div v-html="pageContent"></div>
</template>

<script lang="ts">
import Vue from 'vue';
export default Vue.extend( {
	name: 'StaticPage',
	props: [ 'pageContent' ],
} );
</script>
