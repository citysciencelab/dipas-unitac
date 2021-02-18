/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for FooterElement is described here.
 * @displayName FooterElement
 */
import {mapGetters} from "vuex";
import NavigationElement from "./NavigationElement.vue";
import DipasButton from "./DipasButton.vue";

export default {
  /**
   * More Informations for the FooterElement is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "FooterElement",
  components: {
    NavigationElement,
    DipasButton
  },
  computed: {
    /**
     * computed ToDo
     */
    ...mapGetters([
      "mainmenu",
      "footermenu",
      "footertext",
      "projectRunning",
      "takesNewContributions"
    ])
  }
};
</script>

<template>
  <section
    class="footer"
    :style="{'backgroundColor': ($root.isMobile ? this.$store.getters.leadingColor : '')}"
  >
    <div
      v-if="!$root.isMobile"
      class="row"
    >
      <NavigationElement
        :links="footermenu"
        class="col-xl-6 col-lg-6 col-md-6 footermenu"
      />

      <p class="col-xl-6 col-lg-6 col-md-6 footertext">
        {{ footertext }}
      </p>
    </div>

    <div v-if="$root.isMobile">
      <!--
        triggered on click
        @event click
      -->
      <DipasButton
        v-if="$route.meta.hasCreateButton && projectRunning && takesNewContributions"
        class="red angular"
        icon="add"
        text="Beitrag erstellen"
        @click="$root.$emit('createContribution')"
      />

      <NavigationElement
        class="quicklinks"
        :links="mainmenu"
      />
    </div>
  </section>
</template>

<style>
    section.footer {
        z-index: 1;
    }

    #app.desktop section.footer {
        background-color: #FAFAFA;
        border-top: 2px solid #fff;
        padding: 0 8px 0;
        box-shadow: 0px 15px 10px -15px #BFBFBF,
                    15px -15px 10px -15px #BFBFBF;
    }

    #app.desktop section.footer p.footertext {
        display: inline-block;
        text-align: right;
        padding-right: 40px;
    }

    #app.mobile section.footer {
        margin: 0;
        padding: 0;
    }
</style>

