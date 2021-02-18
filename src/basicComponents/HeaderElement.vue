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
   * computed ToDo
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
    filterButton () {
      return this.$route.path === "/contributionlist";
    }
  },
  watch: {
    $route () {
      this.showMenu = false;
      this.$scrollTo({offset: 0}, 300, {container: "section.content"});
    }
  }
};
</script>

<template>
  <section
    class="row header"
    :class="{aboveAll: showMenu}"
  >
    <div
      class="menuHeader"
      :style="{'backgroundColor': this.$store.getters.leadingColor}"
    >
      <div
        v-if="!$root.isMobile"
        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 logoline"
      >
        <router-link to="/">
          <img
            v-if="homeButtonLogo !== ''"
            class="HomeButtonLogo d-none d-sm-block"
            :src="homeButtonLogo"
          />

          <p class="project-title d-none d-sm-block">
            {{ projecttitle }}
          </p>
        </router-link>

        <img
          v-if="menuLineLogo !== ''"
          class="HamburgBug d-none d-sm-block"
          :src="menuLineLogo"
        />
      </div>
      <!--
        triggered on click
        @event click
      -->
      <div
        v-if="$root.isMobile"
        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 menuToggle"
        @click="showMenu = !showMenu"
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

        <img
          v-if="showMenu && homeButtonLogo !== ''"
          class="HomeButtonLogo d-sm-block"
          :src="homeButtonLogo"
        />
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
        class="filterButton"
        :class="{filterButtonActive: $root.showFilter}"
        @click="$root.showFilter = !$root.showFilter"
      >
        <i class="material-icons">filter_list</i>
      </div>
    </div>

    <div
      v-if="!$root.isMobile || ($root.isMobile && showMenu)"
      class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 menuContents"
      :style="{'backgroundColor': ($root.isMobile? '#E3E3E3': this.$store.getters.leadingColor)}"
    >
      <NavigationElement
        :links="mainmenu"
        class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 headermenu"
        :class="{mobileMenu: $root.isMobile}"
      />

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
          class="footertext"
        >
          {{ footertext }}
        </p>
      </div>
    </div>
  </section>
</template>

<style>
    @import "https://fonts.googleapis.com/icon?family=Material+Icons";
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

    section.header div.menuHeader div.logoline img.HomeButtonLogo {
        margin-left: 7px;
        margin-bottom: 20px;
        float: left;
        max-height: 40px;
    }

    section.header div.menuHeader div.logoline p.project-title {
        float: left;
        color: #003064;
        font-size: 23px;
        white-space: nowrap;
        margin-left: 10px;
        margin-top: 7px;
    }

    section.header div.menuHeader div.logoline img.HamburgBug {
        position: absolute;
        left: 0;
        bottom: -20px;
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
        min-height: 40px;
        height: auto;
        margin: 10px 0 0 0;
        padding: 8px 0 4px 0;
    }

    #app.mobile section.header div.menuContents {
        background-color: #E3E3E3;
        height: calc((var(--vh, 1vh) * 100) - 50px);
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
        font-size: 20px;
        float: left;
        display: block;
        width: 75%;
        margin: 10px 0 10px 10px;
    }

    #app.mobile section.header div.menuContents div.menuFooter {
        padding: 20px 20px 30px 20px;
        bottom: 0;
        width: 100%;
    }

    #app.mobile section.header div.menuContents div.menuFooter p.footertext {
        color: white;
        font-size: 16px;
        margin: 0;
    }
</style>
