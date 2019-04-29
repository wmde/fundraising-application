<template>
    <div class="navbar is-fixed-top has-background-bright has-shadow">
        <div class="container">
            <div class="navbar-brand">
                <a class="navbar-item" href="/">
                    <img :src="assetsPath + '/images/logo-horizontal-wikimedia.svg'" alt="Wikimedia Deutschland"
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
                    <a v-for="( link, index ) in headerMenu" 
                        :key="index" 
                        :href="link.url" 
                        v-bind:class="[{ 'active': link.id === pageIdentifier }, 'navbar-item']">
                        {{ $t( 'menu_item_' + link.localeId ) }}
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
	props: [ 'assetsPath', 'pageIdentifier' ],
	data: function () {
		return {
			showNavbarBurger: false,
			'headerMenu': [
				{ id: 'donation-form', localeId: 'donate', url: '/' },
				{ id: 'membership-application', localeId: 'membership_application', url: '/apply-for-membership' },
				{ id: 'faq-page', localeId: 'faq', url: '/faq' },
				{ id: 'use-of-funds', localeId: 'use_of_resources', url: '/use-of-funds' },
			],
		};
	},
} );
</script>

<style lang="scss">
    @import "../../scss/variables";
    .navbar {
        &-item {
            border-bottom: 2px solid $fun-color-bright;
        }
        &-menu {
            .navbar-item {
                &:hover {
                    border-bottom: 2px solid $fun-color-primary;
                }
                &:active {
                    background-color: $fun-color-primary-lightest;
                }
                &.active {
                    border-bottom: 2px solid $fun-color-primary;
                    color: $fun-color-primary;
                    font-weight: bold;
                }
            }
        }
    }
</style>
