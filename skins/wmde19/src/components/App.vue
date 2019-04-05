<template>
  <section id="app" class="section">
    <div class="container">
      <Header></Header>
      <Content>
        <slot></slot>
      </Content>
    </div>
  </section>
</template>

<script lang="ts">
import Vue from 'vue';
import Header from './layout/Header.vue';
import Content from './layout/Content.vue';

export default Vue.extend( {
	name: 'app',
	components: {
		Header,
		Content,
	},
} );
</script>
