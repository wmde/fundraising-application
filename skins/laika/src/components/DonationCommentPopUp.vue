<template>
    <div class="modal-card">
        <p class="modal-card-title has-margin-bottom-18">{{ $t( 'donation_comment_popup_title' ) }}</p><br>
        <p class="has-margin-bottom-18">{{ $t( 'donation_comment_popup_explanation' ) }}</p>
        <b-field :label="$t( 'donation_comment_popup_label' )">
            <b-input maxlength="200" type="textarea"></b-input>
        </b-field>
        <div class="field has-margin-bottom-18">
            <b-checkbox v-model="commentIsAnon">{{ $t( 'donation_comment_popup_is_anon' ) }}</b-checkbox>
        </div>
        <div class="field has-margin-bottom-18">
            <b-checkbox v-model="commentIsPublic">{{ $t( 'donation_comment_popup_is_public' ) }}</b-checkbox>
        </div>
        <div class="columns">
            <div class="column">
            <b-button type="is-primary is-main has-margin-top-18 level-item" @click="$parent.close()" outlined>
                {{ $t( 'donation_comment_popup_cancel' ) }}
            </b-button>
            </div>
            <div class="column">
            <b-button type="is-primary is-main has-margin-top-18 level-item" @click="postComment">
                {{ $t( 'donation_comment_popup_submit' ) }}
            </b-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue'

    export default Vue.extend( {
        name: 'DonationCommentPopUp',
        data: function() {
            return {
                commentIsPublic: true,
                commentIsAnon: false,
            }
        },
        methods: {
            postComment() {
                //TODO
            },
        }
    } )
</script>