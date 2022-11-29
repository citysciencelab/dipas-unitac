/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The global Modal Element.
 * @displayName ModalEelement
 */
export default {
  /**
   * More Informations for the ModalElement is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "ModalElement",
  data () {
    return {
      zoom: ""
    };
  },
  mounted () {
    this.updatePixelRatio();
    this.$refs.content.focus();

    const focusableElements = "button, [href], input, select, textarea, [tabindex]:not([tabindex='-1'])",
      modal = this.$refs.content,
      firstFocusableElement = modal.querySelectorAll(focusableElements)[0],
      focusableContent = modal.querySelectorAll(focusableElements),
      lastFocusableElement = focusableContent[focusableContent.length - 1];

    document.addEventListener("keydown", function (e) {
      const isTabPressed = e.key === "Tab" || e.keyCode === 9;

      if (!isTabPressed) {
        return;
      }

      if (e.shiftKey) {
        if (document.activeElement === firstFocusableElement) {
          lastFocusableElement.focus();
          e.preventDefault();
        }
      }
      else if (document.activeElement === lastFocusableElement) {
        firstFocusableElement.focus();
        e.preventDefault();
      }
    });
  },
  methods: {
    /**
     * Close the Modal element
     * @event closeModal
     * @returns {void}
     */
    closeModal ($event) {
      $event.stopPropagation();
      /**
       * Close the modal
       */
      this.$emit("closeModal");
    },
    updatePixelRatio () {
      const pixelRatio = window.devicePixelRatio;

      switch (true) {
        case pixelRatio >= 1.25 && pixelRatio < 1.50:
          this.zoom = "zoom125";
          break;
        case pixelRatio >= 1.50 && pixelRatio < 1.75:
          this.zoom = "zoom150";
          break;
        case pixelRatio >= 1.75 && pixelRatio < 2:
          this.zoom = "zoom175";
          break;
        case pixelRatio >= 2:
          this.zoom = "zoom200";
          break;
        default:
          this.zoom = "";
      }

      matchMedia(`(resolution: ${pixelRatio}dppx)`).addEventListener("change", this.updatePixelRatio, {once: true});
    }
  }
};
</script>

<template>
  <div
    class="customModal"
    :class="{modalMobile: $root.isMobile}"
  >
    <div
      ref="content"
      class="modalContent"
      :class="zoom"
      role="dialog"
      tabindex="0"
      @keyup.esc="closeModal"
    >
      <!--
        triggered on click
        @event click closeModal
      -->
      <button
        ref="closeButton"
        tabindex="0"
        class="closebutton"
        :aria-label="$t('Schedule.closeButton')"
        @click="closeModal"
      >
        <i
          aria-hidden="true"
          class="material-icons"
        >
          close
        </i>
      </button>
      <!-- @slot Use this slot content-->
      <slot></slot>
    </div>
    <div
      class="modalBackground"
      tabindex="0"
      @keyup.esc="closeModal"
    >
    </div>
  </div>
</template>

<style>
  div.customModal {
      position: fixed;
      left: 0;
      top: 0;
      height: 100%;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999999;
  }

  div.customModal div.modalBackground {
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 100%;
      background-color: black;
      opacity: 0.5;
      pointer-events: all;
      z-index: 1000;
  }

  div.customModal div.modalContent {
      position: relative;
      background-color: white;
      border: none 0 transparent;
      width: fit-content;
      height: fit-content;
      overflow-x: hidden;
      overflow-y: auto;
      padding: 30px;
      z-index: 1001;
      box-shadow: 5px 5px 10px -5px #000000;
  }

  #app.mobile div.customModal.createContributionModal div.modalContent {
      padding: 30px 16px 30px 16px;
  }

  div.customModal div.modalContent div.checkbox-wrapper label {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 366px;
  }

  #app.mobile div.customModal div.modalContent div.checkbox-wrapper label {
      width: 99%;
  }

  div.customModal.noPadding div.modalContent {
      padding: 0;
  }

  div.customModal.modalMobile div.modalContent {
      width: 100%;
      height: 100%;
  }

  div.customModal div.modalContent button.closebutton {
      position: absolute;
      top: 0.1rem;
      right: 0.3rem;
      border: none 0 transparent;
      background-color: transparent;
      margin: 0;
      padding: 0;
      width: 1.875rem;
      height: 1.875rem;
      background: #FFFFFF;
      border-radius: 0.938rem;
  }

  div.customModal div.modalContent button.closebutton:focus:not(:focus-visible)  {
      outline: none;
  }

  div.customModal div.modalContent button.closebutton:focus-visible {
      outline: 3px solid #005CA9;
      outline-offset: -3px;
      border-radius: 0.938rem;
  }

  .customModal .modalContent:focus-visible {
      outline-color: transparent;
  }

  div.customModal .modalContent button.dipasButton:focus-visible {
      outline: 3px solid #005CA9;
      outline-offset: 4px;
  }

  div.customModal .modalContent button.dipasButton {
      font-size: 1.25rem;
      line-height: 0.8rem;
  }
  div.modalContent.zoom125 {
    zoom: 0.8;
  }
  div.modalContent.zoom150 {
    zoom: 0.75;
  }
  div.modalContent.zoom175 {
    zoom: 0.7;
  }
  div.modalContent.zoom200 {
    zoom: 0.65;
  }
</style>
