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
  methods: {
    /**
     * Close the Modal element
     * @returns {void}
     */
    closeModal ($event) {
      $event.stopPropagation();
      /**
       * Close the modal
       */
      this.$emit("closeModal");
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
    >
      <!--
        triggered on click
        @event click
      -->
      <button
        ref="closeButton"
        class="closebutton"
        @click="closeModal"
      >
        <i class="material-icons">close</i>
      </button>
      <!-- @slot Use this slot content-->
      <slot></slot>
    </div>
    <div class="modalBackground"></div>
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

    div.customModal div.modalContent button.closebutton:focus {
        outline: none;
    }
</style>
