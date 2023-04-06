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
      text: "",
      showBanner: true
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
     * Gets called when user clicks "rejet cookies" or closes the Cookie Banner
     * @returns {void}
     */
    declineCookies: function () {
      this.$root.cookieBannerConfirmed = false;
      this.showBanner = false;
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
    v-if="!hasCookie && !$root.cookieBannerConfirmed && showBanner"
    class="cookieBanner"
    role="dialog"
  >
    <!--
    close the cookie banner in mobile state
    @event click
    -->
    <button
      :aria-label="$t('Cookiebar.closeButton')"
      class="closeButton"
      @click="declineCookies"
    >
      <i
        aria-hidden="true"
        class="material-icons"
      >
        close
      </i>
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
          tabindex="0"
          @click="gotoToDataprivacyPage()"
          @keyup.enter="gotoToDataprivacyPage()"
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
        :text="$t('Cookiebar.acceptButtonText')"
        class="blue angular"
        role="button"
        tabindex="0"
        @click="acceptCookies"
      />
      <!--
        @name DipasButton
        @event click decline cookies, cloose banner
      -->
      <DipasButton
        :text="$t('Cookiebar.declineButtonText')"
        class="red angular"
        role="button"
        tabindex="0"
        @click="declineCookies"
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

    #app.mobile div.cookieBanner button.closeButton {
        background: #FFFFFF;
        color:#003063;
    }

    div.cookieBanner button.closeButton {
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
        border-radius: 15px;
        color:white;
    }

    div.cookieBanner button.closeButton:focus:not(:focus-visible) {
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

    div.cookieBanner div.text span.dataPrivacyLink:focus-visible {
      padding: 3px;
      outline: 1px solid #ffffff;
      margin: auto 4px;
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
        margin: 0 25px;
        display: flex;
        gap: 1rem;
    }

    #app.mobile div.cookieBanner div.buttons {
        margin-left:0;
        width: 100%;
        position: absolute;
        bottom: 0;
        left: 0;
        padding:1rem;
    }

    #app.mobile div.cookieBanner div.buttons button:first-child {
      margin-bottom:1rem;
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
