/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas project logo block.
 * @displayName ProjectlogoBlock
 */
export default {
  /**
   * More Informations for the project logo block is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "ProjectlogoBlock",
  computed: {
    /**
     * computed ToDo
     */
    projectlogo () {
      return this.$store.getters.projectlogo;
    }
  }
};
</script>

<template>
  <section v-if="projectlogo">
    <img :src="projectlogo" />
  </section>
</template>
