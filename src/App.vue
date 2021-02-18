/**
 * @license GPL-2.0-or-later
 */

<script>
import Vue from "vue";
import {requestBroker} from "./mixins/requestBroker.js";
import HeaderElement from "./basicComponents/HeaderElement.vue";
import FooterElement from "./basicComponents/FooterElement.vue";
import WrapperElement from "./basicComponents/WrapperElement.vue";
import CookieBanner from "./basicComponents/CookieBanner.vue";
import FirstModal from "./basicComponents/FirstModal.vue";
import CreateContributionModal from "./basicComponents/CreateContributionModal.vue";
import MaintenanceMessage from "./basicComponents/MaintenanceMessage.vue";
import {mapGetters} from "vuex";

export default {
  name: "App",
  components: {
    CookieBanner,
    CreateContributionModal,
    FooterElement,
    HeaderElement,
    MaintenanceMessage,
    WrapperElement
  },
  mixins: [requestBroker],
  computed: {
    ...mapGetters([
      "maintenanceMode",
      "maintenanceMessage",
      "redirectURL",
      "takesNewContributions",
      "frontpage"
    ])
  },
  watch: {
    redirectURL (val) {
      if (val !== null && val !== undefined) {
        window.location.href = val;
      }
    }
  },
  created () {
    this.initialize().then(() => {
      if (this.$route.meta.openContributionModal && this.takesNewContributions) {
        this.$root.showCreateContributionModal = true;
      }
    });

    this.$root.$on("createContribution", function () {
      this.$root.showCreateContributionModal = true;
    }.bind(this));
  },
  methods: {
    wrapperMounted () {
      const FirstModalComponent = Vue.extend(FirstModal),
        FirstModalInstance = new FirstModalComponent({parent: this.$refs.wrapper});

      FirstModalInstance.$mount();
      this.$refs.wrapper.$el.appendChild(FirstModalInstance.$el);
    }
  }
};
</script>

<template>
  <div
    id="app"
    :class="{mobile: $root.isMobile, desktop: !$root.isMobile}"
  >
    <MaintenanceMessage
      v-if="maintenanceMode"
      :message="maintenanceMessage"
    />
    <template v-else>
      <HeaderElement />
      <WrapperElement
        ref="wrapper"
        :class="frontpage"
        @mounted="wrapperMounted"
      />
      <FooterElement />
      <CreateContributionModal v-if="$root.showCreateContributionModal" />
      <CookieBanner />
    </template>
  </div>
</template>
