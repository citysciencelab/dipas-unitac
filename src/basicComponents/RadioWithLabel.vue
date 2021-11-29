/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas radio with label component.
 * @displayName RadioWithLabel
 */
import _ from "underscore";

export default {
  /**
   * More Informations for RadioWithLabel is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "RadioWithLabel",
  model: {
    prop: "checked"
  },
  props: {
    /**
     * Checked state.
     * @name checked
     */
    checked: {
      type: [Number, String],
      default: ""
    },
    /**
     * The value of radio with number and value
     * @name value
     */
    value: {
      type: [Number, String],
      default: ""
    },
    /**
     * Label text
     * @name label
     */
    label: {
      type: String,
      default: ""
    },
    /**
     * icon of radio.
     * @name icon
     */
    icon: {
      type: String,
      default: ""
    },
    /**
     * direction of text
     * @name horizontal
     */
    horizontal: {
      type: String,
      default: ""
    },
    groupId: {
      type: String,
      default: ""
    }
  },
  data () {
    return {
      uniqueId: _.uniqueId("rb-")
    };
  },
  computed: {
    /**
     * serves the internal value of the checkbox
     * @name internalValue
     * @returns {Boolean} checked
     */
    internalValue: {
      get () {
        return this.checked;
      },
      set (val) {
        this.$emit("input", val);
      }
    },
    /**
     * serves the label style
     * @name labelStyle
     * @returns {String} labelStyle
     */
    labelStyle: function () {
      return this.icon ? "background-image: url(" + this.icon + ")" : "";
    }
  }
};
</script>

<template>
  <div
    class="radio-wrapper"
    :class="{'radio-wrapper-horizontal': horizontal === 'true'}"
  >
    <input
      :id="uniqueId"
      v-model="internalValue"
      tabindex="0"
      type="radio"
      :name="groupId"
      :value="value"
    />
    <label
      :for="uniqueId"
      :class="{withIcon: icon}"
    >
      <span :style="labelStyle">{{ label }}</span>
    </label>
  </div>
</template>

<style>
    div.radio-wrapper {
        margin-bottom: 10px;
    }

    div.radio-wrapper.radio-wrapper-horizontal {
        float: left;
        margin-right: 20px;
    }

    div.radio-wrapper input {
        position: absolute;
        opacity: 0;
        z-index: -1;
    }

    div.radio-wrapper label {
        font-size: 1.063rem;
        line-height: 20px;
        margin: 0;
        padding: 0;
        cursor: pointer;
        white-space: nowrap;
    }

    div.radio-wrapper label:before {
        content: "";
        font-size: 0.563rem;
        color: white;
        line-height: 1rem;
        text-align: center;
        vertical-align: text-bottom;
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 10px;
        border: solid 1px #005CA9;
        border-radius: 10px;
        background-color: white;
        cursor: pointer;
    }

    div.radio-wrapper input[type="radio"]:checked + label:before {
        content: "\2B24";
        background-color: #005CA9;
    }

    div.radio-wrapper label.withIcon span {
        padding-left: 20px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: top left;
    }
</style>
