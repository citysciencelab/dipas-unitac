/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas radio group component.
 * @displayName RadioGroup
 */
import RadioWithLabel from "./RadioWithLabel.vue";

export default {
  /**
   * More Informations for RadioGroup is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "RadioGroup",
  components: {
    RadioWithLabel
  },
  props: {
    /**
     * Values of radios.
     */
    value: {
      type: [Number, String],
      default: ""
    },
    /**
     * Options for the radios
     */
    options: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * Icons for the radios
     */
    icons: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * Horizontal radios
     * @values true, false
     */
    horizontal: {
      type: String,
      default: "false"
    }
  },
  data () {
    return {
      selected: this.value,
      labelIcons: this.icons !== undefined ? this.icons : []
    };
  },
  watch: {
    selected: function (val) {
      this.$emit("input", val);
    },
    value: function (val) {
      this.selected = val;
    }
  }
};
</script>

<template>
  <div class="radiogroup">
    <RadioWithLabel
      v-for="(option, val, index) in options"
      :key="val"
      v-model="selected"
      :label="option.label"
      :icon="labelIcons[val] ? labelIcons[val].icon : ''"
      :value="option.val"
      :horizontal="(index < Object.keys(options).length-1) || $root.isMobile ? horizontal : 'false'"
    />
    <!-- complex horizontal definition to allow different classes for last element of horizontal radio group-->
  </div>
</template>
