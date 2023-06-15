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
     * serves the project owner
     * @returns {String} project owner
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
    <h3 class="headline">
      {{ $t("Contact.headline") }}
    </h3>
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
      <p class="phone">
        <a
          :aria-label="$t('Contact.phone')"
        >
          <i
            class="material-icons material-icons-style"
            aria-hidden="true"
          >
            phone
          </i> {{ projectowner.telephone }}</a>
      </p>
      <p class="address">
        <a
          :href="'mailto:' + projectowner.email"
          :aria-label="$t('Contact.email')"
        >
          <i
            class="material-icons material-icons-style"
            aria-hidden="true"
          >
            email
          </i>&nbsp;{{ projectowner.email }}</a>
      </p>
      <p class="address">
        <a
          :href="projectowner.website"
          :aria-label="$t('Contact.web')"
          target="_blank"
        >
          <i
            class="material-icons material-icons-style"
            aria-hidden="true"
          >
            public
          </i>&nbsp;{{ projectowner.website }}
        </a>
      </p>
    </div>
  </section>
</template>

<style>
    div.contactInfo a {
        text-overflow: ellipsis;
        overflow: hidden;
        display: inline-block;
        width: 100%;
        white-space: nowrap;
        color: #005CA9;
        font-weight: bold;
    }

    div.contactInfo  a:hover {
        white-space: normal;
        word-wrap: break-word;
        text-decoration: none;
    }

    div.contactInfo .address {
      margin-top: -1em;
    }

    div.contactInfo .phone {
      color: #005CA9;
    }

    div.contactInfo .material-icons-style {
        display: inline;
    }
</style>

