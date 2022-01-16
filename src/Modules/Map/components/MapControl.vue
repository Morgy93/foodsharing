<template>
  <div id="map-control-wrapper">
    <div
      class="ui-dialog ui-widget ui-widget-content ui-corner-all"
      tabindex="-1"
    >
      <div class="ui-dialog-content ui-widget-content">
        <div id="map-control">
          <ul class="linklist">
            <li>
              <a class="ui-corner-all baskets"><span
                class="fa-stack fa-lg"
                style="color: #72B026"
              ><i class="fas fa-circle fa-stack-2x" /><i class="fas fa-shopping-basket fa-stack-1x fa-inverse" /></span><span>{{ $i18n('terminology.baskets') }}</span></a>
            </li>
            <li>
              <a class="ui-corner-all betriebe"><span
                class="fa-stack fa-lg"
                style="color: #9E3235"
              ><i class="fas fa-circle fa-stack-2x" /><i class="fas fa-shopping-cart fa-stack-1x fa-inverse" /></span><span>{{ $i18n('menu.entry.stores') }}</span></a>
              <div id="map-options">
                <label><input
                  type="checkbox"
                  name="viewopt[]"
                  value="STANDARD"
                > Standard</label>
                <label><input
                  checked="checked"
                  type="checkbox"
                  name="viewopt[]"
                  value="NEED_HELP"
                > {{ $i18n('menu.entry.helpwanted') }}</label>
                <label><input
                  checked="checked"
                  type="checkbox"
                  name="viewopt[]"
                  value="NEED_HELP_INSTANT"
                > {{ $i18n('menu.entry.helpneeded') }}</label>
                <label><input
                  type="checkbox"
                  name="viewopt"
                  value="IN_NEGOTIATION"
                > {{ $i18n('menu.entry.in_negotiation') }}</label>
                <label><input
                  type="checkbox"
                  name="viewopt"
                  value="ALL_STORES"
                > {{ $i18n('store.bread') }}</label>
              </div>
            </li>
            <li>
              <a class="ui-corner-all fairteiler"><span
                class="fa-stack fa-lg"
                style="color: #FFCA92"
              ><i class="fas fa-circle fa-stack-2x" /><i class="fas fa-recycle fa-stack-1x fa-inverse" /></span><span>{{ $i18n('terminology.fsp') }}</span></a>
            </li>
            <li>
              <a class="ui-corner-all communities"><span
                class="fa-stack fa-lg"
                style="color: cornflowerblue"
              ><i class="fas fa-circle fa-stack-2x" /><i class="fas fa-users fa-stack-1x fa-inverse" /></span><span>{{ $i18n('menu.entry.regionalgroups') }}</span></a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {

}
</script>
