import Vue from "vue";
import { Validity } from '../../../src/app/lib/validation/validation_states';

Vue.mixin({
    methods: {
        inputIsValid: (value, pattern ) => {
            if (pattern === null) {
                return value !== '' ? Validity.VALID : Validity.INVALID;
            }
            return new RegExp(pattern).test(value) ? Validity.VALID : Validity.INVALID;
        }
    }
})