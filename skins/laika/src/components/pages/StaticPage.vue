<template>
    <div class="column">
        <h1 class="title is-size-1"> {{ $t(pageId) }}</h1>
        <div v-html="pageContent"></div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
export default Vue.extend( {
	name: 'StaticPage',
	props: [ 'pageContent', 'pageId' ],
} );
</script>
