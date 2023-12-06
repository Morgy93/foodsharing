<template>
  <div>
    <div class="head ui-widget-header">
      {{ $i18n('foodsaver.delete_account') }}
    </div>

    <div class="ui-widget-content corner-bottom margin-bottom ui-padding">
      {{ $i18n('foodsaver.delete_own_account') }}

      <ul>
        <li><a href="/?page=legal">{{ $i18n('legal.if_delete.legal_1') }}</a></li>
        <li><a href="https://www.dsgvo.tools/aufbewahrungsfristen">{{ $i18n('legal.if_delete.legal_2') }}</a></li>
      </ul>

      <div
        class="alert alert-secondary"
        role="alert"
      >
        {{ $i18n('legal.if_delete.this_gets_deleted_main') }}
        <ul>
          <li>{{ $i18n('legal.if_delete.this_gets_deleted_stores') }}</li>
          <li>{{ $i18n('legal.if_delete.this_gets_deleted_quiz') }}</li>
          <li>{{ $i18n('legal.if_delete.this_gets_deleted_verify') }}</li>
          <li>{{ $i18n('legal.if_delete.this_gets_deleted_friendlist') }}</li>
          <li>{{ $i18n('legal.if_delete.this_gets_deleted_trustbananas') }}</li>
        </ul>
      </div>
      <div
        class="alert alert-warning"
        role="alert"
      >
        {{ $i18n('legal.if_delete.this_doesnt_get_deleted') }}
        <ul>
          <li>{{ $i18n('legal.if_delete.this_doesnt_get_deleted_name') }}</li>
          <li>{{ $i18n('legal.if_delete.this_doesnt_get_deleted_address') }}</li>
          <li>{{ $i18n('legal.if_delete.this_doesnt_get_deleted_history') }}</li>
        </ul>
      </div>
      <b-button
        id="delete-account"
        variant="danger"
        @click="$refs.modal_account_deletion.show()"
      >
        {{ $i18n('foodsaver.delete_account_now') }}
      </b-button>
    </div>
    <b-modal
      id="modal-delete-account"
      ref="modal_account_deletion"
      :title="$i18n('foodsaver.delete_account')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('foodsaver.delete_account')"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="tryDeleteAccount"
    >
      {{ $i18n('foodsaver.delete_account_sure') }}
    </b-modal>
  </div>
</template>

<script>
import { deleteUser } from '@/api/user'
import { goTo, pulseError, pulseSuccess } from '@/script'
import i18n from '@/helper/i18n'

export default {
  props: {
    userId: { type: Number, required: true },
  },
  methods: {
    async tryDeleteAccount () {
      try {
        await deleteUser(this.userId, null)
        pulseSuccess(i18n('success'))
        goTo('/?page=logout')
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>
