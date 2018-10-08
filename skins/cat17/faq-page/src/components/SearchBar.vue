<template>
<div id="search">
	<span>{{ messageSearch }}</span>
	<form class="inline" action="">
	<input type="text" name="search"></input>
	<button type="button"><i class="icon-search"></i></button>
	</form>
</div>
</template>

<script>
export default {
	name: 'search',
	props: [ 'messageSearch' ]
};
</script>
