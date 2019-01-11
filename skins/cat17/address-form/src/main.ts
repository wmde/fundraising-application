import Vue from "vue";
import AddressForm from "./AddressForm.vue";
import store from "./store";

Vue.config.productionTip = false;

new Vue({
  store,
  render: h => h(AddressForm)
}).$mount("#updateAddress");
