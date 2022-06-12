<template>
  <!-- eslint-disable-next-line vue/max-attributes-per-line -->
  <b-container
    fluid
    class="bootstrap bg-white pt-5 pb-5"
  >
    <b-container>
      <b-row>
        <b-col
          xl="3"
          sm="6"
          cols="12"
        >
          <h2>{{ $i18n('footer.call_to_action') }}</h2>
          <ul class="nav flex-column">
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
                :href="$url('devdocs')"
                :aria-label="$i18n('footer.infosDeveloper')"
              >
                {{ $i18n('footer.infosDeveloper') }}
              </a>
            </li>
          </ul>
        </b-col>
        <b-col
          xl="3"
          sm="6"
          cols="12"
          class="pt-5 pt-sm-0"
        >
          <h2>{{ $i18n('footer.about_us') }}</h2>
          <b-row>
            <b-col
              sm="4"
              cols="3"
            >
              <ul class="nav flex-column">
                <li class="nav-item">
                  <a
                    :href="$url('team')"
                    :aria-label="$i18n('footer.team')"
                  >
                    {{ $i18n('footer.team') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('partner')"
                    :aria-label="$i18n('footer.partner')"
                  >
                    {{ $i18n('footer.partner') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('contact')"
                    :aria-label="$i18n('footer.contact')"
                  >
                    {{ $i18n('footer.contact') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('press')"
                    :aria-label="$i18n('footer.press')"
                  >
                    {{ $i18n('footer.press') }}
                  </a>
                </li>
              </ul>
            </b-col>
            <b-col
              xl="2"
              cols="3"
            >
              <ul class="nav flex-column">
                <li class="nav-item">
                  <a
                    :href="$url('statistics')"
                    :aria-label="$i18n('footer.statistics')"
                  >
                    {{ $i18n('footer.statistics') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('wiki')"
                    :aria-label="$i18n('footer.wiki')"
                  >
                    {{ $i18n('footer.wiki') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('blog')"
                    :aria-label="$i18n('footer.blog')"
                  >
                    {{ $i18n('footer.blog') }}
                  </a>
                </li>
              </ul>
            </b-col>
          </b-row>
        </b-col>
        <b-col
          xl="6"
          cols="12"
          class="pt-4 pt-xl-5 pt-sm-0"
        >
          <b-row>
            <b-col
              v-if="!isFsDotAt"
              xl="6"
              cols="12"
            >
              <p>{{ $i18n('footer.our_partner') }}</p>
              <a
                :href="$url('hoster')"
                target="_blank"
                :rel="externalLink"
                data-toggle="tooltip"
                data-placement="top"
                :title="$i18n('footer.hosting_tooltip')"
              >
                <img
                  height="25px"
                  src="/img/footer/manitu_logo.svg"
                ></a>
              <a
                :href="$url('denns')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  src="/img/footer/denns_logo_at.png"
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
                  class="pl-4 pt-4"
                  :alt="$i18n('footer.wiener_tafel_alt_text')"
                >
              </a>
            </b-col>
            <b-col
              v-else
              sm="6"
              cols="12"
            >
              <p>{{ $i18n('footer.hosting_info') }}</p>
              <a
                :href="$url('hoster')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  height="49px"
                  :alt="$i18n('footer.hosting_info')"
                  src="/img/footer/manitu_logo.svg"
                >
              </a>
            </b-col>
            <b-col
              sm="6"
              cols="12"
              class="pt-xl-0 pt-5"
            >
              <ul>
                <li>
                  <p>{{ $i18n('footer.call_to_donate') }}</p>
                </li>
                <li>
                  <b-button
                    block
                    variant="secondary"
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
      <div class="line pt-3 pt-xl-5" />
      <b-row class="pt-3">
        <b-col
          xl="8"
          cols="6"
        >
          <b-row>
            <b-col
              xl="4"
              sm="8"
              cols="3"
            >
              <a
                :href="$url('imprint')"
                :aria-label="$i18n('footer.imprint')"
                class="pr-sm-4"
              >
                {{ $i18n('footer.imprint') }}</a>
              <a
                :href="$url('dataprivacy')"
                :aria-label="$i18n('footer.dataprivacy')"
              >
                {{ $i18n('footer.dataprivacy') }}
              </a>
            </b-col>
            <b-col
              xl="3"
              sm="4"
            >
              <a
                v-if="!isFsDotAt"
                class="social_icons"
                :href="twitterUrl"
                target="_blank"
                title="Twitter"
                :rel="externalLink"
              >
                <i class="fab fa-twitter-square pr-2" />
              </a>
              <a
                class="social_icons"
                :href="facebookUrl"
                target="_blank"
                title="Facebook"
                :rel="externalLink"
              >
                <i class="fab fa-facebook-square pr-2" />
              </a>
              <a
                class="social_icons"
                :href="instagramUrl"
                target="_blank"
                title="Instagram"
                :rel="externalLink"
              >
                <i class="fab fa-instagram-square pr-2" />
              </a>
              <a
                class="social_icons"
                :href="linkedinUrl"
                title="LinkedIn"
                target="_blank"
                :rel="externalLink"
              >
                <i class="fab fa-linkedin pr-2" />
              </a>
              <a
                class="social_icons"
                :href="youtubeUrl"
                title="YouTube"
                target="_blank"
                :rel="externalLink"
              >
                <i class="fab fa-youtube-square" />
              </a>
            </b-col>
          </b-row>
        </b-col>
        <b-col
          xl="4"
          cols="6"
          class="d-flex justify-content-end"
        >
          <p>
            <a
              v-if="isBeta || isDev"
              :href="betaTestingIssues"
            >
              {{ $i18n('footer.beta_testing_issues') }}
            </a>
            <a
              v-else
              :href="$url('releaseNotes')"
            >
              {{ $i18n('releases.2022-05') }}
            </a>
            <br>
            <a :href="$url('devdocs')">
              {{ $i18n('footer.it_devdocs') }}
            </a>
          </p>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <div class="text-right text-truncate">
            <span
              v-if="srcRevision && isBeta"
              class="rev-link"
            >
              <a :href="revisionLink">
                {{ $i18n('footer.version', {version: srcRevision}) }}
              </a>
            </span>
          </div>
        </b-col>
      </b-row>
    </b-container>
  </b-container>
</template>

<script>
export default {
  props: {
    isFsDotAt: {
      type: Boolean,
      default: false,
    },
    srcRevision: {
      type: String,
      default: null,
    },
    isBeta: {
      type: Boolean,
      default: false,
    },
    isDev: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    revisionLink () {
      return this.isBeta ? 'https://gitlab.com/foodsharing-dev/foodsharing/tree/' + this.srcRevision : ''
    },
    facebookUrl () {
      return this.isFsDotAt ? 'https://www.facebook.com/oesterreichfoodsharing' : 'https://www.facebook.com/foodsharing.de'
    },
    instagramUrl () {
      return this.isFsDotAt ? 'https://instagram.com/foodsharing.at' : 'https://www.instagram.com/foodsharing_de'
    },
    twitterUrl () {
      return 'https://twitter.com/FoodsharingDE'
    },
    linkedinUrl () {
      return 'https://www.linkedin.com/company/foodsharingde'
    },
    youtubeUrl () {
      return 'https://www.youtube.com/user/foodsharingtv'
    },
    betaTestingIssues () {
      return 'https://beta.foodsharing.de/?page=bezirk&bid=734&sub=forum'
    },
    externalLink () {
      return 'nofollow noreferrer noopener'
    },
  },
}
</script>

<style lang="scss" scoped>
.bootstrap .nav-link {
  padding: unset;
}

.social_icons {
  color: var(--fs-light-grey);
  font-size: 1.2rem;
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
  color: var(--fs-dark-grey);
}

.line {
  border-bottom: 1px solid var(--fs-dark-grey);
}

a, p, li {
  font-size: 1rem;
  line-height: 2rem;
  color: var(--fs-black);
}

p {
  line-height: 1rem;
}
</style>