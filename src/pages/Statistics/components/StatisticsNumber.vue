/**
 * @license GPL-2.0-or-later
 */

<script>
export default {
  name: "StatisticsNumber",
  props: {
    headline: {
      type: String,
      default: ""
    },
    value: {
      type: Number,
      default: 0
    }
  }
};
</script>

<template>
  <div class="number">
    <h3 class="headline">
      {{ headline }}
    </h3>
    <p class="number">
      {{ value }}
    </p>
  </div>
</template>
