<template>
  <div class="groups">
    <div
      v-for="group in groups"
      :key="group.id"
      class="field"
    >
      <Accordion>
        <template #header>
          <span>
            {{ group.name }}
            <i
              v-if="group.function_tooltip_key && group.function_tooltip_key"
              class="fas fa-info-circle fa-fw"
              :title="$i18n(group.function_tooltip_key)"
            />
          </span>
        </template>

        <div>
          <div class="members">
            <a
              v-for="leader in group.leaders"
              :key="leader.id"
              class="member"
              :href="'/profile/' + leader.id"
            >
              <img
                :src="leader.image"
                :alt="leader.name"
              >
            </a>
            <div><strong>{{ $i18n('group.admin_count_' + clamp(group.leaders.length, 2), {count: group.leaders.length}) }}</strong></div>
            <div>{{ $i18n('group.member_count_' + clamp(group.members.length, 2), {count: group.members.length }) }}</div>
          </div>

          <div
            v-if="group.image"
            class="photo"
          >
            <a
              class="fancybox"
              :href="group.image"
            >
              <img
                :src="group.image"
                :alt="$i18n('group.image', { name: group.name })"
              >
            </a>
          </div>

          <div class="teaser">
            <span v-html="nl2br(group.teaser)" />
            <p style="margin-top:15px;">
              <strong><a :href="'mailto:' + group.email">{{ group.email }}</a></strong>
            </p>
          </div>

          <div class="clear" />
          <div class="bottom_bar">
            <div
              v-if="group.appliedFor"
              class="ui-padding"
            >
              {% include 'partials/info.twig' with {'msg': 'group.already_applied'|trans} only %}
            </div>
            <template v-else-if="group.applicationRequirementsNotFullfilled">
              {{ $i18n('group.application_requirements.not_fulfilled', { bananas: group.applyMinBananaCount, weeks: group.applyMinFoodsaverWeeks, fetches: group.applyMinFetchCount }) }}
              <div class="mb-3" />
            </template>

            <div class="button-interactions">
              <a
                v-if="group.email.length"
                class="button"
                href="#"
                :onclick="'ajreq(\'contactgroup\',{id:' + group.id + '});return false;'"
              >
                {{ $i18n('group.actions.contact') }}
              </a>
              <a
                v-if="group.mayEdit"
                class="button"
                :href="'/?page=groups&sub=edit&id='+ group.id"
              >
                {{ $i18n('group.actions.edit') }}
              </a>
              <a
                v-if="group.mayAccess"
                class="button"
                :href="'/?page=bezirk&bid='+ group.id"
              >
                {{ $i18n('group.actions.go') }}
              </a>
              <a
                v-if="group.mayApply"
                class="button"
                href="#"
                :onclick="'ajreq(\'apply\',{id:' + group.id + '});'"
              >
                {{ $i18n('group.actions.apply') }}
              </a>
              <a
                v-if="group.mayJoin"
                class="button"
                href="#"
                :onclick="'ajreq(\'addtogroup\',{id:' + group.id + '});'"
              >
                {{ $i18n('group.actions.join') }}
              </a>
              <div class="clear" />
            </div>
          </div>
        </div>
      </Accordion>
    </div>
  </div>
</template>

<script>
import Accordion from '@/components/Container/accordion/Accordion.vue'
import { nl2br } from '@/utils.js'

export default {
  components: { Accordion },
  props: {
    groups: {
      type: Array,
      default (rawProps) {
        return []
      },
    },
  },
  methods: {
    clamp: function (value, limit) {
      if (isNaN(value)) {
        return 0
      }
      return Math.min(limit, Math.max(0, value))
    },
    nl2br: nl2br,
  },
}
</script>
