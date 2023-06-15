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
     * @name links
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
    <nav>
      <ul :class="'navigation-length-' + Object.keys(links).length">
        <li
          v-for="(text, endpoint) in links"
          :key="endpoint"
        >
          <!--
          @name NavigationLink
          -->
          <NavigationLink
            :text="text"
            :endpoint="endpoint"
          />
        </li>
      </ul>
    </nav>
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
        text-transform: uppercase;
    }

    section.navigation ul li {
        display: inline-block;
        margin: 0;
    }

    #app.desktop section.navigation.headermenu ul li {
        padding-top: 14px;
        height: 46px;
        line-height: 23px;
    }

    #app.mobile section.navigation ul li {
        display: block;
    }

    #app.mobile section.navigation.headermenu ul li {
        background-color: rgb(0, 48, 100);
    }

    #app.mobile section.navigation ul li:not(:last-child) {
        border-bottom: solid 1px #EEEEEE;
    }

    #app.mobile section.navigation ul li:last-child {
        border-bottom: solid 1px #071849;
    }

    #app.mobile section.navigation ul li:first-child {
        border-top: solid 1px #EEEEEE;
    }

    section.navigation ul li a {
        font-size: 1rem;
        font-weight: bold;
        color: white;
        text-decoration: none;
    }

    #app.mobile section.navigation ul li a {
        font-family: 'Roboto Condensed', sans-serif;
        font-size: 1.313rem;
        padding: 1px 0 12px 20px;
        display: block;
    }

    section.navigation.headermenu ul li a {
        padding: 16px 12px 11px 9px;
    }

    section.navigation.headermenu ul li a:hover,
    section.navigation.headermenu ul li a:focus-visible {
        outline: 3px solid #ffffff;
        outline-offset: -3px;
    }

    section.navigation ul li a.router-link-active {
        color: #003064;
        background-color: #ffffff;
    }

    #app.desktop section.navigation ul li a.router-link-active {
        padding: 16px 12px 11px 9px;
    }

    section.navigation ul li a.router-link-active:hover,
    section.navigation ul li a.router-link-active:focus-visible {
        outline: 2px solid #003063;
        outline-offset: -4px;
    }

    section.navigation ul li a span.material-icons {
        font-size: 1rem;
        position: relative;
        top: 2px;
        margin-right: 5px;
    }

    #app.mobile section.navigation ul li a span.material-icons {
        font-size: 2.25rem;
        top: 10px;
    }

    #app.desktop section.navigation.footermenu ul {
        margin-left: 25px;
    }

    #app.mobile section.navigation.footermenu ul {
        text-transform: none;
    }

    #app.desktop section.navigation.footermenu ul li {
        margin-right: 20px;
    }

    section.navigation.footermenu ul li a {
        color: #005CA9;
        font-weight: bold;
    }

    #app.mobile section.navigation.footermenu ul li a {
        padding-top: 10px;
    }

    #app.desktop section.navigation.footermenu ul li a.router-link-active {
        outline: 1px solid #005CA9;
        outline-offset: 2px;
        padding: 0 0 0 0;
    }


    #app.desktop section.navigation.footermenu ul li a.router-link-active:hover,
    #app.desktop section.navigation.footermenu ul li a.router-link-active:focus-visible {
        outline: 3px solid #003063;
        outline-offset: 1px;
        padding: 0 0 0 0;
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

    #app.mobile section.navigation.quicklinks ul li a span.linktext {
        display: none;
    }

    #app.mobile section.navigation.quicklinks ul li a span.material-icons {
        font-size: 1.875rem;
        color: #FFFFFF;
    }

    #app.mobile section.navigation.quicklinks ul li a.router-link-active span.material-icons {
        color: #003064;
    }
    #app.mobile section.navigation.quicklinks ul li a.router-link-active {
        background-color: #ffffff;
        outline: 1px solid #005CA9;
        outline-offset: -2px;
    }
</style>
