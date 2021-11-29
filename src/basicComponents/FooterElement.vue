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
     * spreads the properties from the vuex store
     */
    ...mapGetters([
      "mainmenu",
      "footermenu",
      "projectRunning",
      "takesNewContributions"
    ])
  }
};
</script>

<template>
  <footer>
    <section
      class="footer"
      :style="{'backgroundColor': ($root.isMobile ? this.$store.getters.leadingColor : '')}"
    >
      <div
        v-if="!$root.isMobile"
        class="row"
      >
        <!--
        @name NavigationElement
      -->
        <NavigationElement
          :links="footermenu"
          class="col-xl-6 col-lg-6 col-md-6 footermenu"
        />
        <div class="col-xl-6 col-lg-6 col-md-6">
          <p class="col-xl-12 col-lg-12 col-md-12 footertext">
            {{ $t("Site.footerText") }}
          </p>
          <a
            href="https://dipas.org"
            class="footerlink"
            target="_blank"
            :title="$t('Site.footerDipasLogoTitle')"
          >
            <img
              src="../../dipaslogo.svg"
              class="footerlogo"
              :alt="$t('Site.footerDipasLogoAltText')"
            >
          </a>
        </div>
      </div>

      <div v-if="$root.isMobile">
        <!--
          @name DipasButton
          @event click createContribution
        -->
        <DipasButton
          v-if="$route.meta.hasCreateButton && projectRunning && takesNewContributions"
          class="red angular"
          icon="add"
          text="Beitrag erstellen"
          @click="$root.$emit('createContribution')"
        />
        <!--
          @name NavigationElement
        -->
        <NavigationElement
          class="quicklinks"
          :links="mainmenu"
        />
      </div>
    </section>
  </footer>
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
        padding-right: 4.8rem;
        font-size: 0.9rem;
    }

    #app.desktop section.footer img.footerlogo {
        width: 2.15rem;
        padding-top: 0px;
    }

    #app.desktop section.footer a.footerlink{
        position: absolute;
        top: 0;
        right: 50px;
    }

    #app.mobile section.footer {
        margin: 0;
        padding: 0;
    }

    #app.mobile footer button.dipasButton:focus-visible {
        outline: 3px solid #fff;
        outline-offset: -5px;
    }
</style>
