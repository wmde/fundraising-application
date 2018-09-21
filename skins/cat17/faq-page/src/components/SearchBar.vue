<template>
<div id="search">
	<span>Suche</span>
	<form class="inline" action="">
	<input type="text" name="search"></input>
	<button type="button"><i class="icon-search"></i></button>
	</form>
</div>
</template>

<script>

export default {
	name: 'search'
}
</script>

<style scoped>
#search form {
	display: flex;
	flex-direction: row;
	margin-top: 0px;
}
input[type=text] {
	padding: 5px;
	border: none;
	width: 625px;
	height: 40px;
	background-color: #cfdbe2;
	font-size: 17px;
}

button {
	background-color: white;
	height: 40px;
	border: 1px solid #ddd;
	border-left: 0px;

}

input[type=text], button {
	display: inline-block;
}
</style>
