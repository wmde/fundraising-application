<template>

    <div class="row receipt-opt-out">
        <div class="col-xs-12">
            <div class="field-grp wrap-check">
                <input type="checkbox" id="donation_receipt" name="receiptOptOut" class="jcf" data-jcf='{"wrapNative":true}' v-model="optedOut" >
                <label class="news" for="donation_receipt">
                  {{  messages.receipt_opt_out }}
                </label>
            </div>
            <div class="explanation">
                {{ messages.receipt_opt_out_explanation }}
            </div>
        </div>

    </div>

</template>

<script>
import Vue from 'vue';
export default Vue.extend( {
    name: "ReceiptOptOut",
    props: [ 'receiptOptOut', 'messages' ],
    data: function() {
        return {
            optedOut: this.$props.receiptOptOut
        }
    },
    watch: {
        optedOut() {
            this.$emit( 'input', this.optedOut );
        }
    }
} );
</script>

<style scoped>

</style>