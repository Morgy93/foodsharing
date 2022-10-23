<template>
  <div
    class="avatar"
    :class="[`sleep--${size}`, `avatar--${size}`, {
      'sleeping': isSleeping,
      'auto-scale': autoScale && ![16, 24].includes(size),
    }]"
  >
    <img
      :alt="$i18n('terminology.profile_picture')"
      :src="avatarUrl"
      :class="{
        'rounded': !round,
        'rounded-circle': round
      }"
      style="height: 100%"
      loading="lazy"
    >
  </div>
</template>

<script>

export default {
  props: {
    url: {
      type: String,
      default: null,
    },
    size: {
      type: Number,
      default: 35,
    },
    isSleeping: {
      type: [Number, Boolean],
      default: 0,
    },
    round: {
      type: Boolean,
      default: false,
    },
    autoScale: {
      type: Boolean,
      default: true,
    },
  },
  computed: {
    avatarUrl () {
      const prefix = {
        16: 'mini_q_',
        24: 'mini_q_',
        35: 'mini_q_',
        50: '50_q_',
        130: '130_q_',
      }[this.size] || ''

      if (this.url && !this.url.includes('q_avatar.png')) {
        if (this.url.startsWith('/api/uploads/')) {
          return this.url + `?w=${this.size}&h=${this.size}` // path for pictures uploaded with the new API
        } else {
          return '/images/' + prefix + this.url // backward compatible path for old pictures
        }
      } else {
        return '/img/' + prefix + 'avatar.png'
      }
    },
    wrapperStyle () {
      const styles = {
        height: `${this.size}px`,
        width: `${this.size}px`,
      }
      return styles
    },
  },
}
</script>

<style lang="scss" scoped>
.auto-scale {
  height: 100%;
  width: auto;
}

.avatar {
  position: relative;
  display: inline-block;
  background-size: cover;
}

.avatar--16 {
  display: inline-flex;
  width: 16px;
  height: 16px;
}

.avatar--24 {
  display: inline-flex;
  width: 24px;
  height: 24px;
}

.sleeping::after {
  content: '';
  display: block;
  height: 100%;
  width: 100%;
  background-repeat: no-repeat;
  background-size: contain;
  position: absolute;
  top: 0;
  left: 0;
}

.sleep--16::after {
  background-image: url('/img/sleep35x35.png');
  top: -4px;
  left: -7px;
}

.sleep--24::after {
  background-image: url('/img/sleep35x35.png');
  top: -4px;
  left: -7px;
}

.sleep--35::after {
  background-image: url('/img/sleep35x35.png');
  top: -8px;
  left: -12px;
}

.sleep--50::after {
  background-image: url('/img/sleep50x50.png');
  top: -10px;
  left: -22px;
}

.sleep--130::after {
  background-image: url('/img/sleep130x130.png');
  top: -15px;
  left: -25px;
}
</style>
