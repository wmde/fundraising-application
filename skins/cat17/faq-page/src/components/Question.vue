<template>
<div id="question" class="space-above">
	<p class="title">{{ content }}</p>
</div>
</template>

<script>
export default {
	name: 'question',
	props: [ 'content' ]
};
</script>