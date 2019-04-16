<template>
    <div class="navbar is-fixed-top has-background-bright has-shadow">
        <div class="container">
            <div class="navbar-brand">
                <a class="navbar-item" href="/">
                    <img :src="imagePath + '/images/logo-horizontal-wikimedia.svg'" alt="Wikimedia Deutschland"
                         width="144" height="29">
                </a>
                <a role="button" aria-label="menu" aria-expanded="false" @click="showNavbarBurger = !showNavbarBurger"
                   v-bind:class="[{ 'is-active': showNavbarBurger }, 'navbar-burger']">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </a>
            </div>

            <div id="navMenu" v-bind:class="[{ 'is-active': showNavbarBurger }, 'navbar-menu']"
                 @click="showNavbarBurger = !showNavbarBurger">
                <div class="navbar-start">
                    <a class="navbar-item">
                        Spendenkommentare
                    </a>
                    <a class="navbar-item">
                        Häufige Fragen
                    </a>
                    <a class="navbar-item">
                        Mittelverwendung
                    </a>
                    <a class="navbar-item">
                        Spenden
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'Header',
	props: [ 'imagePath' ],
	data: function () {
		return {
			showNavbarBurger: false,
		};
	},
} );
</script>
