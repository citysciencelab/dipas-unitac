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
     * holds the project logo
     * @name projectlogo
     * @returns {String} projectlogo
     */
    projectlogo () {
      return this.$store.getters.projectlogo;
    }
  }
};
</script>

<template>
  <section v-if="projectlogo.path">
    <img
      :src="projectlogo.path"
      :alt="projectlogo.alttext"
    />
  </section>
</template>
