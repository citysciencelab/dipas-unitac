/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas contact block.
 * @displayName ContactBlock
 */
export default {
  /**
   * More Informations for ContactBlock is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "ContactBlock",
  computed: {
    /**
     * computed ToDo
     */
    projectowner () {
      return this.$store.getters.projectowner;
    }
  }
};
</script>

<template>
  <!--
    Shows the contact data at sidebar
  -->
  <section>
    <p class="headline">
      {{ $t("Contact.headline") }}
    </p>
    <div class="contactInfo">
      <p class="projectOwner">
        {{ projectowner.name }}
      </p>
      <p
        v-if="projectowner.street1"
        class="address"
      >
        {{ projectowner.street1 }}
      </p>
      <p
        v-if="projectowner.street2"
        class="address"
      >
        {{ projectowner.street2 }}
      </p>
      <p
        v-if="projectowner.city"
        class="address"
      >
        {{ projectowner.zip }} {{ projectowner.city }}
      </p>
      <p>
        <a><i class="material-icons">phone</i> {{ projectowner.telephone }}</a><br />
        <a :href="'mailto:' + projectowner.email"><i class="material-icons material-icons-style">email</i>&nbsp;{{ projectowner.email }}</a><br />
        <a
          :href="projectowner.website"
          target="_blank"
        >
          <i class="material-icons material-icons-style">public</i>&nbsp;{{ projectowner.website }}
        </a>
      </p>
    </div>
  </section>
</template>

<style>
    .contactInfo a {
        text-overflow: ellipsis;
        overflow: hidden;
        display: inline-block;
        width: 100%;
        white-space: nowrap;
        color: #212529;
    }

     a:hover {
        white-space: normal;
        word-wrap: break-word;
        text-decoration: none;
    }

    .address {
      margin-top: -1em;
    }

    .material-icons-style {
        display: inline;
        color: #212529;
    }
</style>

