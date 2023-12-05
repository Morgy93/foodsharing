<template>
  <leaflet-location-picker
    :zoom="zoom"
    :coordinates="coordinates"
    :icon="icon"
    :marker-draggable="false"
  />
</template>

<script>
import L from 'leaflet'
import 'leaflet.awesome-markers'
import LeafletLocationPicker from '@/components/map/LeafletLocationPicker'
L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa'

export default {
  components: { LeafletLocationPicker },
  props: {
    zoom: { type: Number, required: true },
    coordinates: { type: Object, required: true },
  },
  data () {
    return {
      icon: L.AwesomeMarkers.icon({ icon: 'shopping-basket', markerColor: 'green' }),
    }
  },
}
</script>
