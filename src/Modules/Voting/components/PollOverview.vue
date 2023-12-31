<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        {{ poll.name }}
      </div>
      <div class="card-body">
        <b-alert
          show
          variant="dark"
        >
          {{ $i18n('polls.hint_2') }}: <a :href="$url('wiki_voting')">{{ $url('wiki_voting') }}</a>
        </b-alert>
        <div
          v-if="mayEdit"
        >
          <b-link
            :href="$url('pollEdit', poll.id)"
            class="btn btn-sm btn-primary mb-3"
          >
            {{ $i18n('poll.edit.title') }}
          </b-link>
          <b-link
            class="btn btn-sm btn-primary mb-3"
            @click="showCancelConfirmDialog"
          >
            {{ $i18n('poll.cancel.title') }}
          </b-link>
        </div>
        <ul class="poll-properties">
          <li class="poll-date">
            <b>{{ $i18n('poll.time_period') }}:</b>
            {{ $dateFormatter.dateTime(startDate) }} - {{ $dateFormatter.dateTime(endDate) }}
            <b-badge v-if="isPollInPast" pill variant="info">
              {{ $i18n('poll.in_past') }}
            </b-badge>
            <b-badge v-else-if="isPollInFuture" pill variant="secondary">
              {{ $i18n('poll.in_future') }}
            </b-badge>
          </li>
          <li class="poll-region">
            <b>{{ $i18n(isWorkGroup ? 'terminology.group' : 'terminology.region') }}:</b> <a :href="$url('polls', regionId)">{{ regionName }}</a>
          </li>
          <li class="poll-scope">
            <b>{{ $i18n('poll.allowed_voters') }}:</b> {{ $i18n('poll.scope_description_'+poll.scope) }}
          </li>
          <li class="poll-scope">
            <b>{{ $i18n('poll.eligible_votes_count') }}:</b> {{ poll.eligibleVotesCount }}
          </li>
          <li class="poll-type">
            <b>{{ $i18n('poll.type') }}:</b> {{ $i18n('poll.type_description_'+poll.type) }}
          </li>
          <li v-if="isPollInPast">
            <b>{{ $i18n('poll.results.percentage_of_votes') }}:</b> {{ percentageTurnout }} %
          </li>
        </ul>

        <div
          v-if="userAlreadyVoted"
          class="my-1 mt-3"
        >
          <b-alert
            show
            variant="dark"
          >
            {{ $i18n('poll.already_voted') }}: {{ $dateFormatter.date(displayedVoteDate) }}
          </b-alert>
        </div>
        <div
          v-else-if="isPollInFuture"
          class="my-1 mt-3"
        >
          <b-alert
            show
            variant="dark"
          >
            {{ $i18n('poll.may_not_yet_vote') }}
          </b-alert>
        </div>
        <div
          v-else-if="!userMayVote && !isPollInPast"
          class="mt-3"
        >
          <b-alert
            show
            variant="dark"
          >
            {{ $i18n('poll.may_not_vote') }}
          </b-alert>
        </div>

        <hr>
        <Markdown :source="poll.description" />
        <hr>

        <VoteForm
          v-if="!isPollInPast"
          :poll="poll"
          :may-vote="userMayVote"
          @vote-callback="userJustVoted"
        />

        <b-alert
          v-if="userVoteDate"
          show
          variant="dark"
        >
          {{ $i18n('poll.untraceable') }}
        </b-alert>

        <ResultsTable
          v-if="isPollInPast"
          :options="poll.options"
          :num-votes="poll.votes"
        />
      </div>
    </div>
  </div>
</template>

<script>
import VoteForm from './VoteForm'
import ResultsTable from './ResultsTable'
import Markdown from '@/components/Markdown/Markdown'
import { BAlert, BLink, BBadge } from 'bootstrap-vue'
import { deletePoll } from '@/api/voting'
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/helper/i18n'

export default {
  components: { ResultsTable, VoteForm, Markdown, BAlert, BLink, BBadge },
  props: {
    poll: {
      type: Object,
      required: true,
    },
    regionId: {
      type: Number,
      required: true,
    },
    regionName: {
      type: String,
      required: true,
    },
    isWorkGroup: {
      type: Boolean,
      default: false,
    },
    mayVote: {
      type: Boolean,
      default: false,
    },
    userVoteDate: {
      type: Object,
      default: null,
    },
    mayEdit: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      userMayVote: this.mayVote,
      userAlreadyVoted: this.userVoteDate !== null,
      displayedVoteDate: this.userVoteDate ? new Date(Date.parse(this.userVoteDate.date)) : new Date(),
    }
  },
  computed: {
    startDate () {
      return new Date(Date.parse(this.poll.startDate.date))
    },
    endDate () {
      return new Date(Date.parse(this.poll.endDate.date))
    },
    isPollInPast () {
      return this.endDate < new Date()
    },
    isPollInFuture () {
      return this.startDate > new Date()
    },
    percentageTurnout () {
      return parseFloat(this.poll.votes / this.poll.eligibleVotesCount * 100).toFixed(2).toLocaleString()
    },
  },
  methods: {
    userJustVoted () {
      this.userAlreadyVoted = true
      this.userMayVote = false
    },
    async showCancelConfirmDialog (e) {
      e.preventDefault()
      // show confirmation dialog
      const cancel = await this.$bvModal.msgBoxConfirm(i18n('poll.cancel.question'), {
        modalClass: 'bootstrap',
        title: i18n('poll.cancel.title'),
        cancelTitle: i18n('no'),
        okTitle: i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (cancel) {
        showLoader()

        try {
          // cancel poll and redirect to poll list
          await deletePoll(this.poll.id)
          window.location.href = this.$url('polls', this.poll.regionId)
        } catch (e) {
          pulseError(i18n('error_unexpected'))
        }

        hideLoader()
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.prestyled {
  white-space: pre-line;
}

.poll-properties {
  font-size: 0.875rem;

  & > li {
    margin-bottom: 0.25rem;
  }
}

.card-body {
  hr {
    // counter the .card definition of padding: 6px 8px;
    margin-left: -8px;
    margin-right: -8px;
  }

  ::v-deep label {
    max-width: 100%;
  }
}
</style>
