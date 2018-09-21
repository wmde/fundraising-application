<template>
 <div id="faq" class="container">
 	<h2>Häufige Fragen</h2>
 	<h5>Antworten zu ihren Fragen zum Spendenprozess, Wikimedia und Wikipedia</h5>
 	<div class="row">
 	<div class="col-xs-12 col-sm-9">
	  	<search-bar></search-bar>
		<div class="wrapper">
			<div class="form-shadow-wrap">
				<question></question>
				<answer></answer>
				<question></question>
				<answer></answer>
				<question></question>
				<answer></answer>
			</div>
		</div>
		<footer>
			<h5>Sie konnten keine Antwort finden? Fragen sie uns direkt!</h5>
			<p>
				Am effizientesten ist das <a>Frage stellen via Kontaktformular.</a> Wir antworten dann per eMail.<br>
				Sie können auch:<br>
				eine e-Mail an <a>spenden@wikimedia.de</a> senden oder<br>
				unser Spendentelefon anrufen: 030/123456789
			</p>
		</footer>
	</div>
	<div class="sidebar col-xs-12 col-sm-3">
		<h5>Fragen und Antworten zu...</h5>
		<ul>
			<li><a>Spendenbescheinigung</a></li>
			<li><a>Spendenbanern auf Wikipedia</a></li>
			<li><a>Wikimedia Organisation</a></li>
			<li><a>Wikimedia Projekte</a></li>
			<li><a>Spendenverwendung</a></li>
		</ul>
		<h5>Keine Antwort gefunden?</h5>
		<ul>
			<li>Frage stellen via Kontaktformular</li>
			<li>weitere <a>Kontaktmöglichkeiten</a></li>
		</ul>
	</div>
	</div>
</div>
</template>

<script>
import SearchBar from './components/SearchBar.vue'
import Question from './components/Question.vue'
import Answer from './components/Answer.vue'

export default {
	name: 'faq',
	components: {
		SearchBar,
		Question,
		Answer
	}
}
</script>

<style scoped>
#faq a {
	color: #00adee;
}
.wrapper {
	width: 100%;
}
.form-shadow-wrap {
	margin-top: 20px;
	margin-left: 0px;
	padding: 1em 20px 3em 45px;
	width: 100%;
}
.sidebar {
	padding-left: 40px;
}
.sidebar h5 {
	margin-top: 50px;
}

.sidebar ul {
	list-style: none;
}

footer {
	margin-top: 50px;
}
</style>
