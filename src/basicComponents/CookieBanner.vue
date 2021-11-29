/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The cookie banner component.
 * @displayName CookieBanner
 */
import {requestBroker} from "../mixins/requestBroker.js";
import DipasButton from "./DipasButton.vue";

export default {
  /**
   * More Informations for the Coockie Banner is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CookieBanner",
  components: {
    DipasButton
  },
  mixins: [requestBroker],
  data () {
    return {
      text: ""
    };
  },
  computed: {
    /**
     * read cookies from browser
     * @returns {Object} cookie
     */
    hasCookie () {
      return this.$cookies.get("dipas");
    }
  },
  methods: {
    /**
     * Gets called when the user clicks "accept coockies"
     * @returns {void}
     */
    acceptCookies: function () {
      this.$root.cookieBannerConfirmed = true;
      this.confirmCookies();
    },
    /**
     * Gets called when the user clicks "inform"
     * @returns {void}
     */
    gotoToDataprivacyPage: function () {
      this.$root.cookieBannerConfirmed = true;
      this.$root.modalWasShown = true;
      this.$router.push("/dataprivacy");
    }
  }
};
</script>

<template>
  <div
    v-if="!hasCookie && !$root.cookieBannerConfirmed"
    class="cookieBanner"
    role="dialog"
  >
    <!--
    close the cookie banner in mobile state
    @event click
    -->
    <button
      v-if="$root.isMobile"
      class="closebutton"
      @click="$root.cookieBannerConfirmed = true"
    >
      <i class="material-icons">close</i>
    </button>

    <div class="text">
      <p class="headline">
        {{ $t('Cookiebar.title') }}
      </p>
      <p>
        {{ $t('Cookiebar.text') }}
        <span
          class="dataPrivacyLink"
          role="link"
          @click="gotoToDataprivacyPage()"
        >
          {{ $t('Cookiebar.linktext') }}
        </span>
        {{ $t('Cookiebar.text_2ndLine') }}
      </p>
    </div>
    <!--
      triggered on click and accept cookies
      @event click
    -->
    <div class="buttons">
      <!--
        @name DipasButton
        @event click accept cookies
      -->
      <DipasButton
        :text="$t('Cookiebar.buttontext')"
        class="blue angular"
        role="button"
        tabindex="0"
        @click="acceptCookies"
      />
    </div>
  </div>
</template>

<style>
    div.cookieBanner {
        position: fixed;
        left: 0;
        bottom: 0;
        z-index: 9999999999;
        width: 100%;
        background-color: #05305E;
        margin: 0;
        padding: 20px;
        display: flex;
    }

    div.cookieBanner button.closebutton {
        position: absolute;
        top: 5px;
        right: 5px;
        border: none 0 transparent;
        background-color: transparent;
        margin: 0;
        padding: 0;
        width: 30px;
        height: 30px;
        line-height: 42px;
        background: #FFFFFF;
        border-radius: 15px;
    }

    div.cookieBanner button.closebutton:focus:not(:focus-visible) {
        outline: none;
    }

    #app.mobile div.cookieBanner {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        background-color: white;
        display: block;
        padding: 40px;
    }

    div.cookieBanner > div {
        display: inline-block;
    }

    #app.mobile div.cookieBanner > div {
        display: block;
    }

    div.cookieBanner div.text {
        flex-grow: 1;
    }

    div.cookieBanner div.text span.dataPrivacyLink {
        font-weight: bold;
        text-decoration: none;
    }

    div.cookieBanner div.text span.dataPrivacyLink:hover {
        text-decoration: underline;
    }

    div.cookieBanner div.text p,
    div.cookieBanner div.text p a {
        color: white;
        font-size: 0.75rem;
        margin: 0;
    }

    #app.mobile div.cookieBanner div.text p,
    #app.mobile div.cookieBanner div.text p a {
        font-size: 1rem;
        color: black;
    }

    div.cookieBanner div.text p a {
        font-weight: bold;
    }

    div.cookieBanner div.text p.headline {
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 10px;
    }

    #app.mobile div.cookieBanner div.text p.headline {
        font-size: 1.5rem;
        color: #003063;
        margin-bottom: 20px;
    }

    div.cookieBanner div.buttons {
        width: auto;
        margin-left: 20px;
    }

    #app.mobile div.cookieBanner div.buttons {
        margin-left: 0;
        width: 100%;
        position: absolute;
        bottom: 0;
        left: 0;
        padding: 40px;
    }

    div.cookieBanner div.buttons button {
        padding: 0 20px;
        height: 40px;
        -webkit-box-shadow: 3px 3px 3px -2px rgba(0, 0, 0, 0.6);
        -moz-box-shadow: 3px 3px 3px -2px rgba(0, 0, 0, 0.6);
        box-shadow: 3px 3px 3px -2px rgba(0, 0, 0, 0.6);
    }

    div.cookieBanner button.dipasButton:focus-visible {
        outline: 3px solid #ffffff;
    }

    #app.mobile div.cookieBanner button.dipasButton:focus-visible {
        outline: 3px solid #ffffff;
        outline-offset: -7px;
    }

</style>
