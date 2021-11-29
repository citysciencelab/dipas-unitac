/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas radio group component.
 * @displayName RadioGroup
 */
import RadioWithLabel from "./RadioWithLabel.vue";
import _ from "underscore";


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
     * holds the values of radios.
     * @name value
     */
    value: {
      type: [Number, String],
      default: ""
    },
    /**
     * Options for the radios
     * @name options
     * @returns {Object} options
     */
    options: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * holds the icons data object for the radios
     * @name icons
     * @returns {Object}
     */
    icons: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * Horizontal radios
     * @name horizontal
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
      labelIcons: this.icons !== undefined ? this.icons : [],
      groupId: _.uniqueId("rgroup-")
    };
  },
  watch: {
    /**
     * @name selected
     * @event input
     */
    selected: function (val) {
      this.$emit("input", val);
    },
    /**
     * set selected
     * @name value
     */
    value: function (val) {
      this.selected = val;
    }
  }
};
</script>

<template>
  <div class="radiogroup">
    <!--
      @name RadioWithLabel
      @property options
      @model selected
    -->
    <RadioWithLabel
      v-for="(option, val, index) in options"
      :key="val"
      v-model="selected"
      :label="option.label"
      :icon="labelIcons[val] ? labelIcons[val].icon : ''"
      :value="option.val"
      :horizontal="(index < Object.keys(options).length-1) || $root.isMobile ? horizontal : 'false'"
      :groupId="groupId"
    />
    <!-- complex horizontal definition to allow different classes for last element of horizontal radio group-->
  </div>
</template>
