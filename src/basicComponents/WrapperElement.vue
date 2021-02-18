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
     * computed ToDo
     */
    projectPhase () {
      return this.$store.getters.projectphase;
    },
    /**
     * computed ToDo
     */
    dipasFront () {
      return this.$store.getters.dipasFront;
    },
    /**
     * computed ToDo
     */
    projectRunning () {
      return this.$store.getters.projectRunning;
    },
    /**
     * computed ToDo
     */
    takesNewContributions () {
      return this.$store.getters.takesNewContributions;
    },
    /**
     * computed ToDo
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
  mounted () {
    this.$emit("mounted");
  },
  methods: {
    /**
     * Scrolling events from wrapper
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
  <section
    :class="pageClass"
    @scroll="onScroll"
  >
    <keep-alive include="contributionList">
      <router-view :key="$route.fullPath" />
    </keep-alive>
  </section>
</template>
