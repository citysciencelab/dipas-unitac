/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for Header element is described here.
 * @displayName HeaderElement
 */
import {mapGetters} from "vuex";
import NavigationElement from "./NavigationElement.vue";

export default {
  /**
   * More Informations for HeaderElement is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "HeaderElement",
  components: {
    NavigationElement
  },
  data () {
    return {
      showMenu: false
    };
  },
  /**
   * spreads the properties from the vuex store
   */
  computed: {
    ...mapGetters([
      "mainmenu",
      "footermenu",
      "footertext",
      "projecttitle",
      "homeButtonLogo",
      "menuLineLogo"
    ]),
    /**
     * botton to offer a filter function
     * @name filterButton
     * @returns {String} route path
     */
    filterButton () {
      return this.$route.path === "/contributionlist";
    }
  },
  watch: {
    /**
     * routing
     * @name $route
     * @returns {void}
     */
    $route () {
      this.showMenu = false;
      this.$scrollTo({offset: 0}, 300, {container: "section.content"});
    }
  }
};
</script>

<template>
  <header>
    <section
      class="row header"
      :class="{aboveAll: showMenu}"
    >
      <div
        class="menuHeader"
        :style="{'backgroundColor': this.$store.getters.leadingColor}"
        tabindex="-1"
      >
        <div
          v-if="!$root.isMobile"
          tabindex="-1"
          class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 logoline"
        >
          <router-link to="/">
            <img
              v-if="homeButtonLogo !== ''"
              class="HomeButtonLogo d-none d-sm-block"
              :src="homeButtonLogo"
              :alt="$t('Site.siteName') + ' – ' + $t('Site.logoLink') + ' ' + projecttitle"
            />

            <p class="project-title d-none d-sm-block">
              {{ projecttitle }}
            </p>
          </router-link>

          <img
            v-if="menuLineLogo !== ''"
            class="HamburgBug d-none d-sm-block"
            :src="menuLineLogo"
            alt=""
          />
        </div>
        <!--
          triggered on click
          @event click
        -->
        <div
          v-if="$root.isMobile"
          class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 menuToggle"
          tabindex="0"
          @click="showMenu = !showMenu"
          @keyup.enter="showMenu = !showMenu"
        >
          <span
            v-if="!showMenu"
            class="threedots"
          >
            <i class="material-icons">more_vert</i>
          </span>

          <span
            v-if="!showMenu"
            class="menustring"
          >
            Menü
          </span>

          <span
            v-if="showMenu"
            class="projecttitle"
          >
            {{ projecttitle }}
          </span>
          <span
            v-if="showMenu"
            class="closeIcon"
          >
            <i class="material-icons">clear</i>
          </span>
        </div>
        <!--
          triggered on click
          @event click
        -->
        <div
          v-if="filterButton && $root.isMobile && !showMenu"
          tabindex="0"
          class="filterButton"
          :class="{filterButtonActive: $root.showFilter}"
          @click="$root.showFilter = !$root.showFilter"
          @keyup.enter="$root.showFilter = !$root.showFilter"
        >
          <i
            :aria-label="$t('ContributionList.ContributionListFilter.filterOptions')"
            class="material-icons"
          >
            filter_list
          </i>
        </div>
      </div>

      <div
        v-if="!$root.isMobile || ($root.isMobile && showMenu)"
        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 menuContents"
        :style="{'backgroundColor': ($root.isMobile? '#E3E3E3': this.$store.getters.leadingColor)}"
      >
        <!--
          @name NavigationElement
        -->
        <NavigationElement
          :links="mainmenu"
          class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 headermenu"
          :class="{mobileMenu: $root.isMobile}"
          tabindex="0"
        />
        <!--
          @name NavigationElement
        -->
        <NavigationElement
          v-if="$root.isMobile"
          :links="footermenu"
          class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 d-block d-md-none footermenu"
          :class="{mobileMenu: $root.isMobile}"
        />

        <div
          class="menuFooter"
          :style="{'backgroundColor': this.$store.getters.leadingColor}"
        >
          <p
            v-if="$root.isMobile"
            class="col-xl-12 col-lg-12 col-md-12 footertext"
          >
            {{ $t("Site.footerText") }}
          </p>
          <a
            v-if="$root.isMobile"
            href="https://dipas.org"
            class="footerlink"
            target="_blank"
            :title="$t('Site.footerDipasLogoTitle')"
          >
            <img
              src="../../dipaslogo_white.svg"
              class="footerlogo"
              :alt="$t('Site.footerDipasLogoAltText')"
            >
          </a>
        </div>
      </div>
    </section>
  </header>
</template>

<style>
    section.header {
        width: 100%;
        height: 115px;
        margin: 0;
        background-color: white;
        position: fixed;
        top: 0;
        z-index: 1;
    }

    section.header.aboveAll {
        z-index: 999999;
    }

    #app.mobile section.header {
        height: 50px;
    }

    section.navigation:focus-visible,
    div.menuHeader {
      outline: transparent;
    }

    section.header div.menuHeader {
        height: 65px;
    }

    #app.mobile section.header div.menuHeader {
        color: white;
        height: auto;
    }

    section.header div.menuHeader div.logoline {
        height: 65px;
        margin: 0;
        padding-top: 10px;
        background-color: #ffffff;
    }

    section.header div.menuHeader div.logoline a {
        display: inline-block;
    }


    section.header div.menuHeader div.logoline img.HomeButtonLogo {
        margin-left: 7px;
        margin-bottom: 20px;
        float: left;
        max-height: 40px;
    }

    section.header div.menuHeader div.logoline p.project-title {
        float: left;
        color: #003063;
        font-size: 1.438rem;
        white-space: nowrap;
        margin-left: 10px;
        margin-top: 2px;
    }

    section.header div.menuHeader div.logoline img.HamburgBug {
        position: absolute;
        left: 0;
        bottom: -24px;
        width: 180px;
        height: 20px;
        z-index: 5;
    }

    section.header div.menuHeader div.menuToggle {
        width: 100vw;
    }

    section.header div.menuHeader div.menuToggle span {
        position: relative;
    }

    section.header div.menuHeader div.menuToggle span.threedots {
        top: 15px;
    }

    section.header div.menuHeader div.menuToggle span.menustring {
        top: 8px;
    }

    section.header div.menuHeader div.filterButton {
        position: absolute;
        top: 13px;
        right: 15px;
    }

    section.header div.menuHeader div.filterButtonActive {
        color: #2B88D8;
    }

    section.header div.menuHeader div.menuToggle span.closeIcon {
        position: absolute;
        top: 13px;
        right: 15px;
        float: right;
        display: block;
    }

    section.header div.menuHeader div.menuToggle span.closeIcon .material-icons {
        font-weight: bold;
    }

    section.header div.menuContents {
        height: auto;
        margin: 15px 0 0 0;
        padding-left: 1px;
        background-color: rgb(0, 48, 100);
        border: rgb(0, 48, 100) 1px solid;
    }

    section.mobileMenu.footermenu nav a:focus-visible {
        outline: 3px solid #005CA9;
        outline-offset: -4px;
    }


    #app.mobile section.header div.menuHeader div:focus-visible {
      outline: 3px solid #ffffff;
      outline-offset: -7px;
      min-height: 3.125rem;
    }

    #app.mobile section.navigation.quicklinks ul li a:focus-visible {
      outline: 3px solid #ffffff;
      outline-offset: -3px;
    }

    #app.mobile section.navigation.quicklinks ul li a.router-link-active:focus-visible {
      outline: 3px solid #005CA9;
      outline-offset: -5px;
    }

    #app.mobile section.header div.menuContents {
        background-color: #E3E3E3;
        height: calc((var(--vh, 1vh) * 100) - 47px);
        margin: 0;
        padding: 0;
        overflow: scroll;
    }

    #app.mobile section.header div.menuHeader img.HomeButtonLogo {
        margin-top: 10px;
        margin-bottom: 10px;
        float: left;
        display: block;
        max-width: 15%;
        max-height: 20px;
    }

    #app.mobile section.header div.menuHeader .projecttitle {
        font-size: 1.125rem;
        float: left;
        display: block;
        width: 75%;
        margin: 10px 0 10px 10px;
    }

    #app.mobile section.header div.menuContents div.menuFooter {
        padding: 20px 20px 30px 20px;
        position: absolute;
        bottom: 0;
        width: 100%;
    }

    #app.mobile section.header div.menuContents div.menuFooter p.footertext {
        color: white;
        font-size: 1rem;
        margin: 0;
        display: inline-block;
        text-align: right;
        padding-right: 2.8rem;
    }

    #app.mobile section.header div.menuContents div.menuFooter a.footerlink {
        position: absolute;
        right: 20px;
    }

    #app.mobile section.header div.menuContents div.menuFooter a.footerlink img {
        width: 2.15rem;
    }
</style>
