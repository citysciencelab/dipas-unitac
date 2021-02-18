/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The First modal.
 * @displayName FirstModal
 */
import moment from "moment";
import DipasButton from "./DipasButton.vue";

export default {
  /**
   * More Informations for the FirstModal is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "FirstModal",
  components: {
    DipasButton
  },
  data () {
    return {
      // Time to pass in minutes since the last visit/reload for the Modal to show up again.
      timeBetweenSplashModals: 15
    };
  },
  computed: {
    /**
     * computed ToDo
     */
    welcomemodal () {
      return this.$store.getters.welcomemodal;
    },
    projectPhase () {
      return this.$store.getters.projectphase;
    },
    showModal () {
      const cookie = this.$cookies.get("dipas");
      let cookieForbidsModal = false,
        vh;

      if (this.$root.isMobile) {
        // calculate viewport height based on Window height and get value of a vh unit
        vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty("--vh", `${vh}px`);

        // add event listener in case the screen is rotated
        window.addEventListener("resize", () => {
          vh = window.innerHeight * 0.01;
          document.documentElement.style.setProperty("--vh", `${vh}px`);
        });
      }

      if (cookie !== null && cookie.hasOwnProperty("splashModalLastShown") && cookie.splashModalLastShown !== undefined) {
        const modalTime = moment(cookie.splashModalLastShown);

        modalTime.add(this.timeBetweenSplashModals, "Minute");
        if (modalTime > moment()) {
          cookieForbidsModal = true;
        }
      }

      return !this.$root.modalWasShown && Object.keys(this.projectperiod).length && !cookieForbidsModal;
    },
    projecttitle () {
      return this.$store.getters.projecttitle;
    },
    projectperiod () {
      return this.$store.getters.projectperiod;
    },
    enabledPhase2 () {
      return this.$store.getters.enabledPhase2;
    },
    /**
     * Determines if showContributionButton should be shown
     * @returns {String} showContributionButton
     */
    showContributionButton () {
      return this.projectPhase === "phase1" && this.$store.getters.takesNewContributions;
    },
    /**
     * Determines if showConceptionButton should be shown
     * @returns {String} showConceptionsButton
     */
    showConceptionsButton () {
      return this.projectPhase === "phase2" || this.projectPhase === "phasemix" || (this.projectPhase === "frozen" && this.enabledPhase2 === true);
    }
  },
  methods: {
    /**
     * Close the modal and saves whether the modal was allways onetime shown
     */
    closeModal () {
      const cookie = this.$cookies.get("dipas");

      if (cookie !== null) {
        cookie.splashModalLastShown = moment().utc().format("YYYY-MM-DD\\THH:mm:ss\\Z");
        this.$cookies.set("dipas", cookie);
      }
      this.$root.modalWasShown = true;
    },
    /**
     * Routes to project-info page and close the FirstModal instantely.
     */
    showProjectInfo () {
      this.closeModal();
      if (this.$router.currentRoute.path !== "/projectinfo") {
        this.$router.push("/projectinfo");
      }
    },
    /**
     * Routes to the createContribution wizard page/modal and close the FirstModal instantely.
     */
    createContribution () {
      this.closeModal();
      this.$root.$emit("createContribution");
    },
    /**
     * Routes to the frontpage and close the FirstModal instantely.
     */
    viewConceptions () {
      this.closeModal();
      this.$router.push("/conceptionlist");
    }
  }
};
</script>

<template>
  <ModalElement
    v-if="showModal"
    class="noPadding firstModal"
    :class="{modalMobile: $root.isMobile}"
    @closeModal="closeModal"
  >
    <div class="firstModalContent">
      <div
        v-if="welcomemodal.image"
        class="modal-image"
        :style="'background-image: url(' + welcomemodal.image + ')'"
      />

      <div class="textcontent">
        <h2>{{ welcomemodal.headline }}</h2>

        <p>{{ welcomemodal.text }}</p>

        <!--
          Shows always the info button; using full width on mobile devices
        -->
        <div class="row buttons">
          <div :class="[(!$root.isMobile && (showContributionButton || showConceptionsButton)) ? 'col-xs-6 col-6' : 'col-xs-12 col-12', 'buttonContainer']">
            <DipasButton
              :text="$t('FirstModal.infoButton')"
              class="grey informButton"
              :class="[$root.isMobile ? 'angular' : 'round']"
              @click="showProjectInfo"
            />
          </div>
          <!--
            Shows the createContribution button only when showContributionButton === true; using full width on mobile devices
          -->
          <div
            v-if="showContributionButton"
            :class="[!$root.isMobile ? 'col-xs-6 col-6' : 'col-xs-12 col-12', 'buttonContainer']"
          >
            <!--
              triggered on click
              @event click
            -->
            <DipasButton
              :text="$t('FirstModal.startButton')"
              icon="add"
              class="red contributeButton"
              :class="[$root.isMobile ? 'angular' : 'round']"
              @click="createContribution"
            />
          </div>

          <!--
            Shows the viewConceptions button only when showConceptionsButton === true; using full width on mobile devices
          -->
          <div
            v-if="showConceptionsButton"
            :class="[!$root.isMobile ? 'col-xs-6 col-6' : 'col-xs-12 col-12', 'buttonContainer']"
          >
            <!--
              triggered on click
              @event click
            -->
            <DipasButton
              :text="$t('FirstModal.conceptionsButton')"
              class="red conceptionButton"
              :class="[$root.isMobile ? 'angular' : 'round']"
              @click="viewConceptions"
            />
          </div>
        </div>
      </div>
    </div>
  </ModalElement>
</template>

<style>
    #app.desktop .firstModalContent {
        width: 720px;
    }

    #app.mobile .firstModalContent {
        height: calc((var(--vh, 1vh) * 100) - 55px);
        overflow-y: auto;
    }

    #app.desktop .firstModalContent .textcontent .buttons .buttonContainer .dipasButton .customIcon {
        margin: 0 40px 2px -70px;
    }

    .firstModal .modal-image {
        min-width: 100%;
        min-height: 350px;
        background-size: 100%;
    }

    #app.mobile .modal-image {
        min-height: auto;
        height: 33vh;
        background-size: cover;
        background-position: center center;
    }

    .firstModal .textcontent {
        padding: 30px 30px 0 30px;
        margin: 10px 0;
    }

    .firstModal .textcontent h2 {
        font-size: 36px;
        font-weight: bold;
    }

    .firstModal .textcontent span.projecttitle {
        white-space: nowrap;
    }

    .firstModal .textcontent .buttons {
        margin-top: 30px;
    }

    #app.desktop .firstModal .textcontent .buttons .buttonContainer:first-child {
        padding-right: 10px;
    }

    #app.desktop .firstModal .textcontent .buttons .buttonContainer:last-child {
        padding-left: 10px;
    }

    #app.mobile .firstModal .textcontent .buttons .buttonContainer:last-child {
        margin-top: 20px;
    }
</style>
