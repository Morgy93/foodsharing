<template>
  <div class="container border border-dark rounded">
    <h4>{{ name }}</h4>
    {{ foodSaverCount }} Foodsaver ({{ foodSaverHomeDistrictCount }} mit Stammbezirk),
    {{ foodSaverHasSleepingHatCount }} Schlafmützen, {{ ambassadorCount }} Botschafter*innen,
    {{ storesCount }} Betriebe, {{ storesCooperationCount }} laufende Kooperationen
    {{ storesFetchedWeight }} kg Lebensmittel gerettet
    Bei {{ storesPickupsCount }} Rettungseinsätzen
  </div>
</template>

<script>

export default {
  props: {
    name: { type: String, required: true },
    foodSaverCount: { type: Number, required: true },
    foodSaverHomeDistrictCount: { type: Number, required: true },
    foodSaverHasSleepingHatCount: { type: Number, required: true },
    ambassadorCount: { type: Number, required: true },
    storesCount: { type: Number, required: true },
    storesCooperationCount: { type: Number, required: true },
    storesPickupsCount: { type: Number, required: true },
    storesFetchedWeight: { type: Number, required: true },
  },
}
</script>
