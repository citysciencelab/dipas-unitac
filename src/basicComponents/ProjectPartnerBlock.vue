/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas project partner block.
 * @displayName ProjectPartnerBlock
 */
export default {
  /**
   * More Informations for the Project Partner Block is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "ProjectPartnerBlock",
  computed: {
    /**
     * holds the project partner logos
     * @name partnerlogos
     * @returns {String} partnerlogos
     */
    partnerlogos () {
      return this.$store.getters.partnerlogos;
    }
  }
};
</script>

<template>
  <section
    v-if="partnerlogos.length"
    class="partnerlogos"
  >
    <h2>{{ $t("ProjectPartnerBlock.headline") }}</h2>
    <div class="row logolist">
      <ul>
        <li
          v-for="(partnerlogo, index) in partnerlogos"
          :key="index"
        >
          <a
            v-if="partnerlogo.logo_link"
            :href="partnerlogo.logo_link"
            target="_blank"
          >
            <img
              :src="partnerlogo.partner_logo"
              :alt="partnerlogo.partner_logo_alttext"
            />
          </a>
          <img
            v-else
            :src="partnerlogo.partner_logo"
            :alt="partnerlogo.partner_logo_alttext"
          />
        </li>
      </ul>
    </div>
  </section>
</template>

<style>
    .partnerlogos {
        padding-top: 45px;
    }

    .partnerlogos h2 {
        font-size: 1.875em;
        line-height: 30px;
        color: #003063;
        font-weight: bold;
    }

    section.partnerlogos ul {
        list-style-type: none;
        list-style-position: outside;
        list-style-image: none;
        margin: 0;
        padding: 0;
    }

    section.partnerlogos ul li {
        display: inline-block;
        margin: 20px 0 0 20px;
        padding: 0;
        vertical-align: top;
    }

    section.partnerlogos ul li a {
        font-size: 1rem;
        color: #BFCBD8;
        text-transform: uppercase;
        text-decoration: none;
    }
</style>
