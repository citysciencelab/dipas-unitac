/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The navigation element component.
 * @displayName NavigationElement
 */
import NavigationLink from "./NavigationLink.vue";

export default {
  /**
   * More Informations for the navigation element is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "NavigationElement",
  components: {
    NavigationLink
  },
  props: {
    /**
     * Object of navigation items
     */
    links: undefined
  },
  data () {
    return {
      showNavigation: false
    };
  }
};
</script>

<template>
  <section class="navigation">
    <ul :class="'navigation-length-' + Object.keys(links).length">
      <li
        v-for="(text, endpoint) in links"
        :key="endpoint"
      >
        <NavigationLink
          :text="text"
          :endpoint="endpoint"
        />
      </li>
    </ul>
  </section>
</template>

<style>
    section.navigation {
        margin: 0;
        padding: 0;
    }

    section.navigation ul {
        list-style-type: none;
        list-style-position: outside;
        list-style-image: none;
        margin: 0;
        padding: 0;
    }

    section.navigation ul li {
        display: inline-block;
        margin: 0;
        padding: 0;
    }

    section.navigation ul li a {
        font-size: 16px;
        color: #BFCBD8;
        text-decoration: none;
    }

    section.header section.navigation ul li a {
        font-family: 'Roboto Condensed', sans-serif;
        font-size: 16px;
        font-weight: bold;
    }

    section.navigation ul li a span.material-icons {
        font-size: 16px;
        position: relative;
        top: 2px;
        margin-right: 5px;
    }

    #app.desktop section.navigation.headermenu ul {
        margin-left: 60px;
        text-transform: uppercase;
    }

    #app.desktop section.headermenu ul li a.router-link-active {
        color: #ffffff;
    }

    #app.desktop section.navigation.footermenu ul {
        margin-left: 120px;
    }

    #app.mobile section.navigation ul li {
        display: block;
    }

    #app.desktop section.navigation.headermenu ul li {
        margin: 4px 40px 4px 0;
    }

    #app.desktop section.navigation.footermenu ul li {
        margin-right: 20px;
    }

    #app.desktop section.navigation.footermenu ul li a {
        color: #005CA9;
    }

    #app.desktop section.navigation.footermenu ul li a.router-link-active {
        font-weight: 600;
    }

    #app.mobile section.navigation ul li a {
        display: block;
        padding: 1px 0 12px 20px;;
        color: black;
        font-size: 21px;
        text-transform: none;
    }

    #app.mobile section.footermenu ul li {
        padding-top: 7px;
    }

    #app.mobile section.navigation ul li a span.material-icons {
        color: #071849;
        font-size: 36px;
        top: 10px;
    }

    #app.mobile section.navigation.headermenu ul li a {
        font-weight: bold;
    }

    #app.mobile section.navigation ul li:not(:last-child) {
        border-bottom: solid 1px #EEEEEE;
    }

    #app.mobile section.navigation.headermenu ul li:last-child {
        border-bottom: solid 1px #071849;
    }

    #app.mobile section.navigation.quicklinks,
    #app.mobile section.navigation.quicklinks ul {
        height: 100%;
    }

    #app.mobile section.navigation.quicklinks ul li {
        display: inline-block;
        height: 100%;
        text-align: center;
        color: #E3E3E3;
        line-height: 36px;
        border-bottom: 0 transparent none;
    }

    #app.mobile section.navigation.quicklinks ul.navigation-length-1 li { width: 100% }
    #app.mobile section.navigation.quicklinks ul.navigation-length-2 li { width: 50% }
    #app.mobile section.navigation.quicklinks ul.navigation-length-3 li { width: 33.3333% }
    #app.mobile section.navigation.quicklinks ul.navigation-length-4 li { width: 25% }
    #app.mobile section.navigation.quicklinks ul.navigation-length-5 li { width: 20% }
    #app.mobile section.navigation.quicklinks ul.navigation-length-6 li { width: 16.6666% }
    #app.mobile section.navigation.quicklinks ul.navigation-length-7 li { width: 14.2857% }

    #app.mobile section.navigation.quicklinks ul li:not(:last-child) {
        border-right: solid 1px #EEEEEE;
    }

    #app.mobile section.navigation.quicklinks ul li a {
        padding: 0;
        display: block;
        line-height: 50px;
    }

    #app.mobile section.navigation.quicklinks ul li a span.material-icons {
        font-size: 30px;
        color: #FFFFFF;
    }

    #app.mobile section.navigation.quicklinks ul li a.router-link-active span.material-icons {
        color: #2B88D8;
    }

    #app.mobile section.navigation.quicklinks ul li a span.linktext {
        display: none;
    }
</style>
