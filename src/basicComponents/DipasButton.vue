/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas button component.
 * @displayName DipasButton
 */
export default {
  /**
   * More Informations for DipasButton is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "DipasButton",
  props: {
    /**
     * The google material icon for the button.
     */
    icon: {
      type: String,
      default: ""
    },
    /**
     * Place the icon right or left.
     */
    iconposition: {
      type: String,
      default: "left"
    },
    /**
       * The Button needs a text.
       */
    text: {
      type: String,
      default: ""
    },
    /**
     * Disables the buttons action.
     */
    disabled: {
      type: Boolean
    },
    /**
     * Alternative hover text.
     */
    disabledText: {
      type: String,
      default: ""
    }
  },
  computed: {
    /**
     * serves the relative icon position
     * @name iconpos
     * @returns {String{left|right}} position
     */
    iconpos () {
      return this.iconposition;
    },
    /**
     * serves the title text
     * @name titleText
     * @returns {String|Null}
     */
    titleText () {
      return this.disabled ? this.disabledText : null;
    }
  }
};
</script>

<template>
  <!--
    @name button
    triggered on click
    @event click
  -->
  <button
    class="dipasButton"
    :disabled="disabled"
    :title="titleText"
    @click="$emit('click')"
  >
    <i
      v-if="icon && iconpos === 'left'"
      class="material-icons customIcon left"
      aria-hidden="true"
    >
      {{ icon }}
    </i>
    {{ text }}
    <i
      v-if="icon && iconpos === 'right'"
      class="material-icons customIcon right"
      aria-hidden="true"
    >
      {{ icon }}
    </i>
  </button>
</template>

<style>
    button.dipasButton {
        display: block;
        text-align: center;
        font-size: 1.25rem;
        line-height: 1rem;
        font-weight: bold;
        cursor: pointer;
        border: none;
        white-space: nowrap;
        width: 100%;
        height: 50px;
    }

    button.dipasButton:focus:not(:focus-visible) {
        outline: none;
    }

    button.dipasButton.red {
        background-color: #E10019;
        color: white;
    }

    button.dipasButton.grey {
        background-color: #E3E3E3;
        color: black;
    }

    button.dipasButton.darkgrey {
        background-color: #767676;
        color: #B3B3B3;
    }

    button.dipasButton.blue {
        background-color: #005CA9;
        color: white;
    }

    button.dipasButton.lightgreen {
        background-color: #DFF6DD;
        color: black;
    }

    button.dipasButton.lightred {
        background-color: #FCE5E7;
        color: black;
    }

    button.dipasButton.angular {
        border-radius: 0px;
        padding: 3px;
    }

    button.dipasButton.round {
        border-radius: 25px;
        padding: 0.3rem 0;
        -webkit-box-shadow: 6px 6px 16px -2px rgba(0, 0, 0, 0.6);
        -moz-box-shadow: 6px 6px 16px -2px rgba(0, 0, 0, 0.6);
        box-shadow: 6px 6px 16px -2px rgba(0, 0, 0, 0.6);
    }

    button.dipasButton .customIcon {
        font-size: 1.5rem;
        font-weight: bold;
        line-height: 1rem;
        vertical-align: middle;
    }

    button.dipasButton .customIcon.left {
        margin: 0 6px 2px -16px;
    }

    button.dipasButton .customIcon.right {
        margin-right: -20px;
        margin-left: 10px;
    }

    button.dipasButton[disabled="disabled"] {
        cursor: default;
        opacity: .5;
    }
</style>

<docs>
DIPAS Button with label and Icon
___

### Examples

The red DIPAS Button rounded:
```jsx
<DipasButton text="Add Entry" class="red contributeButton round" icon="add"></DipasButton>
```
The grey DIPAS Button rounded:
```jsx
<DipasButton text="Please swipe" class="grey contributeButton round" icon="gesture"></DipasButton>
```
The red DIPAS Button disabled:
```jsx
<DipasButton text="Hamburg" class="red contributeButton round" disabled="disabled" icon="anchor"></DipasButton>
```
The red DIPAS Button with angular borders an right icon:
```jsx
<DipasButton text="Close" class="blue contributeButton angular" iconposition="right" icon="close"></DipasButton>
```
</docs>
