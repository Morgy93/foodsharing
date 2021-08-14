<template>
  <div class="activity-container">
    <div class="head ui-widget-header corner-top activities">
      <span v-if="currentFilterDescription">
        {{ $i18n('dashboard.updates_title_some', [$i18n(currentFilterDescription)]) }}
      </span>
    </div>
  </div>
</template>

<script>
export default {
}
</script>

<style lang="scss" scoped>
.activity-container {
  margin-bottom: 1em;
}

.activities {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}

.dashboard-options {
  cursor: pointer;

  > a {
    padding: 2px;
    border-radius: 50%;
    height: 18px;
    width: 18px;
    line-height: 18px;
    text-align: center;
    text-decoration: none;

    &:hover,
    &.active {
      color: var(--fs-brown);
      background-color: var(--fs-white);
    }

    &.wide {
      border-radius: 4px;

      span {
        padding: 2px 4px;
        font-weight: bolder;
      }
    }
  }

  .header-divider {
    border-left: 1px solid var(--fs-beige);
    height: 18px;
    width: 1px;
    display: inline-block;
    vertical-align: middle;
    margin: 0 6px;
  }
}
</style>
