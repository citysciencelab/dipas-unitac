/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The wrapper element.
 * @displayName WrapperElement
 */
export default {
  name: "WrapperElement",
  computed: {
    /**
     * serves the project phase
     * @name projectPhase
     * @returns {String} projectphase
     */
    projectPhase () {
      return this.$store.getters.projectphase;
    },
    /**
     * serves the dipasFront
     * @name dipasFront
     * @returns {String} dipasFront
     */
    dipasFront () {
      return this.$store.getters.dipasFront;
    },
    /**
     * serves wether the project is still running or not
     * @name projectRunning
     * @returns {String|Boolean} projectRunning
     */
    projectRunning () {
      return this.$store.getters.projectRunning;
    },
    /**
     * serves wether takes nwe contributions
     * @name takesNewContributions
     * @returns {Boolean} new contributions
     */
    takesNewContributions () {
      return this.$store.getters.takesNewContributions;
    },
    /**
     * serves the page class
     * @name pageClass
     * @returns {String} pageClass
     */
    pageClass () {
      const classes = [
        "content",
        this.$route.meta.pageClass
      ];
      let vh;

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

      if (!this.$route.meta.hasCreateButton || !this.projectRunning || !this.takesNewContributions) {
        classes.push("noFooterCreateButton");
      }

      if (
        this.$root.isMobile &&
                    (this.projectPhase === "phase1" ||
                    (["phasemix", "frozen"].indexOf(this.projectPhase) !== -1 && this.dipasFront === "phase1")
                    )
      ) {
        classes.push("frontpageWithoutBorder");
      }

      return classes;
    }
  },
  /**
   * @event mounted
   */
  mounted () {
    this.$emit("mounted");
  },
  methods: {
    /**
     * Scrolling events from wrapper
     * @name onScroll
     * @param {number} scrollTop Top of the wrapper
     * @param {number} clientHeight Height of the client window
     * @param {number} scrollHeight Scroll height value
     * @event scrollBottomReached fires if the bottom reached
     * @returns {void}
     */
    onScroll: function ({target: {scrollTop, clientHeight, scrollHeight}}) {
      if (scrollTop + clientHeight >= scrollHeight) {
        this.$root.$emit("scrollBottomReached");
      }
    }
  }
};
</script>

<template>
  <main>
    <section
      :class="pageClass"
      tabindex="-1"
      @scroll="onScroll"
    >
      <keep-alive include="contributionList">
        <router-view :key="$route.fullPath" />
      </keep-alive>
    </section>
  </main>
</template>
