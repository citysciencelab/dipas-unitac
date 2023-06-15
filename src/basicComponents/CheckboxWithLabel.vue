/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The Checkbox with a label.
 * @displayName CheckboxWithLabel
 */
import _ from "underscore";

export default {
  /**
   * More Informations for Checkbox is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CheckboxWithLabel",
  model: {
    prop: "checked"
  },
  props: {
    /**
     * The state of the box.
     */
    checked: {
      type: Array,
      default () {
        return [];
      }
    },
    /**
     * The value of the box.
     */
    value: {
      type: String,
      default: ""
    },
    /**
     * The label beside the box.
     */
    label: {
      type: String,
      default: ""
    },
    /**
     * The material icon beside the box.
     * @see [Google Material Icons](https://material.io/resources/icons/)
     */
    icon: {
      type: String,
      default: ""
    }
  },
  data () {
    return {
      uniqueId: _.uniqueId("cb-")
    };
  },
  computed: {
    /**
     * ToDo
     */
    internalValue: {
      get () {
        return this.checked;
      },
      set (val) {
        this.$emit("input", val);
      }
    },
    labelStyle: function () {
      return this.icon ? "background-image: url(" + this.icon + ")" : "";
    }
  }
};
</script>

<template>
  <div class="checkbox-wrapper">
    <input
      :id="uniqueId"
      v-model="internalValue"
      tabindex="0"
      type="checkbox"
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
    div.checkbox-wrapper input {
        position: absolute;
        opacity: 0;
        z-index: -1;
    }

    div.checkbox-wrapper label {
        cursor: pointer;
        margin-left: 10px;
        position: relative;
        line-height: 20px;
    }

    div.checkbox-wrapper label:before {
        content: "";
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 10px;
        position: relative;
        top: 3px;
        background-color: white;
        border: solid 1px #005CA9;
        font-family: "Material Icons";
        font-size: 1.125rem;
        font-weight: bold;
        line-height: 20px;
        color: white;
        padding-left: 1px;
    }

    div.checkbox-wrapper input:checked + label:before {
        content: "check";
        background-color: #005CA9;
    }

    div.checkbox-wrapper label.withIcon span {
        padding-left: 1.5rem;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: top left;
    }

    div.checkbox-wrapper input:focus-visible + label {
        outline: 3px solid #005CA9;
        outline-offset: 5px;
        opacity: 1;
    }
</style>

<docs>
CheckboxWithLabel example:

```jsx
<CheckboxWithLabel label="Example label"></CheckboxWithLabel>
```
</docs>
