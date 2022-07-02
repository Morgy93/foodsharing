<template>
  <!-- eslint-disable-next-line vue/max-attributes-per-line -->
  <b-container
    fluid
    class="bootstrap bg-white pt-5 pb-5"
  >
    <b-container>
      <b-row class="mb-5">
        <b-col
          md="3"
          cols="6"
        >
          <h2>{{ $i18n('footer.call_to_action') }}</h2>
          <ul class="flex-column">
            <li class="nav-item">
              <a
                :href="$url('infosCompany')"
                :aria-label="$i18n('footer.infosCompany')"
              >
                {{ $i18n('footer.infosCompany') }}
              </a>
            </li>
            <li class="nav-item">
              <a
                :href="$url('joininfo')"
                :aria-label="$i18n('footer.infosPrivatePersons')"
              >
                {{ $i18n('footer.infosPrivatePersons') }}
              </a>
            </li>
            <li class="nav-item">
              <a
                :href="$url('devdocs_it_tasks')"
                :aria-label="$i18n('footer.infosDeveloper')"
              >
                {{ $i18n('footer.infosDeveloper') }}
              </a>
            </li>
          </ul>
        </b-col>
        <b-col
          md="3"
          cols="6"
          class="mb-5 pt-sm-0"
        >
          <h2>{{ $i18n(informationen[0].name) }}</h2>
          <ul class="flex-column">
            <li
              v-for="(information, index) in informationen[0].items"
              :key="index"
              class="nav-item"
            >
              <a
                :href="$url(information.url)"
                :aria-label="$i18n(information.title)"
                v-html="$i18n(information.title)"
              />
            </li>
          </ul>
        </b-col>
        <b-col
          md="6"
          cols="12"
          class="pt-xl-5 pt-sm-0"
        >
          <b-row>
            <b-col
              v-if="isDotAt"
              xl="6"
              cols="12"
            >
              <p>{{ $i18n('footer.our_partner') }}</p>
              <a
                v-b-tooltip="$i18n('footer.hosting_tooltip')"
                :href="$url('hosting')"
                class="hide-external"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  height="25px"
                  :alt="$i18n('footer.hosting_tooltip')"
                  src="/img/footer/manitu_logo.svg"
                  loading="lazy"
                >
              </a>
              <a
                :href="$url('denns')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  src="/img/footer/denns_logo_at.png"
                  loading="lazy"
                  class="pl-4"
                  :alt="$i18n('footer.denns_at_alt_text')"
                >
              </a>
              <br>
              <a
                :href="$url('bmlfuw')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  src="/img/footer/BMLRT_Logo.png"
                  loading="lazy"
                  :alt="$i18n('footer.lebensministerium_alt_text')"
                  class="pt-4"
                >
              </a>
              <a
                :href="$url('wiener_tafel')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  src="/img/footer/wiener_tafel_logo_at.png"
                  loading="lazy"
                  class="pl-4 pt-4"
                  :alt="$i18n('footer.wiener_tafel_alt_text')"
                >
              </a>
            </b-col>
            <b-col
              v-else
              sm="6"
              cols="12"
              class="mb-5"
            >
              <p>{{ $i18n('footer.hosting_info') }}</p>
              <a
                v-b-tooltip="$i18n('footer.hosting_tooltip')"
                :href="$url('hosting')"
                class="hide-external"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  height="49px"
                  loading="lazy"
                  class="img-fluid"
                  :alt="$i18n('footer.hosting_tooltip')"
                  src="/img/footer/manitu_logo.svg"
                >
              </a>
            </b-col>
            <b-col
              sm="6"
              cols="12"
              class="pt-xl-0"
            >
              <ul>
                <li>
                  <p>{{ $i18n('footer.call_to_donate') }}</p>
                </li>
                <li>
                  <b-button
                    block
                    variant="primary"
                    class="w-75"
                    :href="$url('donations')"
                  >
                    {{ $i18n('footer.donate_now') }}
                  </b-button>
                </li>
              </ul>
            </b-col>
          </b-row>
        </b-col>
      </b-row>
      <b-row>
        <b-col
          md="3"
          cols="6"
          class="mb-5 mb-xl-0"
        >
          <h2>{{ $i18n(informationen[1].name) }}</h2>
          <ul class="flex-column">
            <li
              v-for="(information, index) in informationen[1].items"
              :key="index"
              class="nav-item"
            >
              <a
                :href="$url(information.url)"
                :aria-label="$i18n(information.title)"
                v-html="$i18n(information.title)"
              />
            </li>
          </ul>
        </b-col>
        <b-col
          md="3"
          cols="6"
          class="mb-5 mb-xl-0"
        >
          <h2>{{ $i18n(informationen[2].name) }}</h2>
          <ul class="flex-column">
            <li
              v-for="(information, index) in informationen[2].items"
              :key="index"
              class="nav-item"
            >
              <a
                :href="$url(information.url)"
                :aria-label="$i18n(information.title)"
                v-html="$i18n(information.title)"
              />
            </li>
          </ul>
        </b-col>
        <b-col
          xl="2"
          md="2"
          cols="6"
          class="mb-5 mb-xl-0"
        >
          <h2>{{ $i18n(informationen[3].name) }}</h2>
          <ul class="flex-column">
            <li
              v-for="(information, index) in informationen[3].items"
              :key="index"
              class="nav-item"
            >
              <a
                :href="$url(information.url)"
                :aria-label="$i18n(information.title)"
                v-html="$i18n(information.title)"
              />
            </li>
          </ul>
        </b-col>
        <b-col
          xl="2"
          md="2"
          cols="6"
          class="mb-5 mb-xl-0"
        >
          <h2>{{ $i18n(informationen[4].name) }}</h2>
          <ul class="flex-column">
            <li
              v-for="(information, index) in informationen[4].items"
              :key="index"
              class="nav-item"
            >
              <a
                :href="$url(information.url)"
                :aria-label="$i18n(information.title)"
                v-html="$i18n(information.title)"
              />
            </li>
          </ul>
        </b-col>
        <b-col
          xl="2"
          md="2"
          cols="12"
          class="mb-5 mb-xl-0"
        >
          <h2>{{ $i18n(informationen[5].name) }}</h2>
          <ul class="flex-column">
            <li
              v-for="(information, index) in informationen[5].items"
              :key="index"
              class="nav-item"
            >
              <a
                :href="$url(information.url)"
                :aria-label="$i18n(information.title)"
                v-html="$i18n(information.title)"
              />
            </li>
          </ul>
        </b-col>
      </b-row>
      <div class="line pt-3 pt-xl-5" />
      <b-row
        class="mt-3 justify-content-between"
      >
        <b-col
          md="6"
          cols="12"
          class="mb-5 mb-md-0"
        >
          <b-row
            class="col"
          >
            <a
              :href="$url('imprint')"
              :aria-label="$i18n('footer.imprint')"
              class="mr-3"
            >
              {{ $i18n('footer.imprint') }}</a>
            <a
              :href="$url('dataprivacy')"
              :aria-label="$i18n('footer.dataprivacy')"
              class="mr-3"
            >
              {{ $i18n('footer.dataprivacy') }}
            </a>
            <a
              :href="$url('contact')"
              :aria-label="$i18n('menu.entry.contact')"
              class="mr-3"
            >
              {{ $i18n('menu.entry.contact') }}
            </a>
          </b-row>
          <b-row
            v-if="!isDotAt"
            class="col"
          >
            <a
              v-for="(social, index) in socials"
              :key="index"
              v-b-tooltip="social.name"
              :href="$url(social.url + '_'+ (isDotAt ? 'at' : 'de'))"
              class="social_icons hide-external"
              :rel="externalLink"
            >
              <i :class="social.icon" />
              <span
                class="sr-only"
                v-html="social.name"
              />
            </a>
          </b-row>
        </b-col>
        <b-col
          md="6"
          cols="12"
          class="d-flex flex-column align-items-md-end"
        >
          <a
            v-if="isBeta || isDev"
            :href="$url('beta_testing_forum')"
          >
            {{ $i18n('footer.beta_testing_issues') }}
          </a>
          <a
            v-else
            :href="$url('release_notes')"
            v-html="$i18n('releases.2022-05')"
          />
          <a
            :href="$url('devdocs')"
            v-html="$i18n('footer.it_devdocs')"
          />
          <a
            v-if="version && isBeta"
            class="text-truncate"
            :href="$url('git_revision', version)"
            v-html="$i18n('footer.version', { version })"
          />
        </b-col>
      </b-row>
    </b-container>
  </b-container>
</template>

<script>
import RouteCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'

export default {
  mixins: [RouteCheckMixin],
  props: {
    version: {
      type: String,
      default: 'cf107753e219b5af997f1f22ff92839fcf754091',
    },
  },
  data () {
    return {
      externalLink: 'nofollow noreferrer noopener',
      socials: [
        {
          name: 'Twitter',
          url: 'twitter',
          icon: 'fab fa-twitter',
        },
        {
          name: 'Facebook',
          url: 'facebook',
          icon: 'fab fa-facebook',
        },
        {
          name: 'Instagram',
          url: 'instagram',
          icon: 'fab fa-instagram',
        },
        {
          name: 'LinkedIn',
          url: 'linkedin',
          icon: 'fab fa-linkedin',
        },
        {
          name: 'YouTube',
          url: 'youtube',
          icon: 'fab fa-youtube',
        },
        {
          name: 'TikTok',
          url: 'tiktok',
          icon: 'fab fa-tiktok',
        },
      ],
      informationen: [
        {
          name: 'menu.entry.regionalgroups',
          items: [
            { url: 'communitiesGermany', title: 'menu.entry.Germany' },
            { url: 'communitiesAustria', title: 'menu.entry.Austria' },
            { url: 'communitiesSwitzerland', title: 'menu.entry.Swiss' },
            { url: 'international', title: 'menu.entry.international' },
          ],
        },
        {
          name: 'menu.entry.aboutUs',
          items: [
            { url: 'mission', title: 'menu.entry.mission' },
            { url: 'wiki_grundsaetze', title: 'menu.entry.fundamentals' },
            { url: 'blog', title: 'menu.entry.blog' },
            { url: 'team', title: 'menu.entry.team' },
            { url: 'partner', title: 'menu.entry.partners' },
            { url: 'press', title: 'menu.entry.press' },
          ],
        },
        {
          name: 'menu.entry.background',
          items: [
            { url: 'freshdesk', title: 'menu.entry.support', target: '_blank' },
            { url: 'wiki', title: 'menu.entry.wiki' },
            { url: 'wiki_guide', title: 'menu.entry.guide', target: '_blank' },
            { url: 'statistics', title: 'menu.entry.statistics' },
            { url: 'release_notes', title: 'menu.entry.release-notes' },
          ],
        },
        {
          name: 'menu.entry.fundraising',
          items: [
            { url: 'donations', title: 'menu.entry.donations' },
            { url: 'circle_of_friends', title: 'menu.entry.friendcircle' },
            { url: 'selfservice', title: 'menu.entry.selfservice' },
            { url: 'transparency', title: 'menu.entry.transparency' },
          ],
        },
        {
          name: 'menu.entry.politics',
          items: [
            { url: 'fsstaedte', title: 'menu.entry.fscities' },
            { url: 'claims', title: 'menu.entry.demands' },
            { url: 'leeretonne', title: 'menu.entry.pastcampaigns' },
          ],
        },
        {
          name: 'menu.entry.education',
          items: [
            { url: 'academy', title: 'menu.entry.academy' },
            { url: 'workshops', title: 'menu.entry.talksandworkshops' },
            { url: 'festival', title: 'menu.entry.fsfestival' },
          ],
        },
      ],
    }
  },
}
</script>

<style lang="scss" scoped>
.bootstrap .nav-link {
  padding: unset;
}

.social_icons {
  color: var(--fs-color-gray-500);
  font-size: 1.2rem;
  padding: .25rem;

  :not(:last-child) {
    margin-right: .5rem;
  }
}

ul {
  list-style-type: none;
  margin-left: 0;
}

h2 {
  font-family: Alfa Slab One,serif;
  font-style: normal;
  font-weight: normal;
  font-size: 1.1rem;
  color: var(--fs-color-gray-600);
}

.line {
  border-bottom: 1px solid var(--fs-color-gray-600);
}

a, p, li {
  font-size: .8rem;
  line-height: 1.8em;
  color: var(--fs-color-gray-900);
}

p {
  line-height: 1rem;
}
</style>
