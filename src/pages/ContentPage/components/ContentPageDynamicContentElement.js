/**
 * @license GPL-2.0-or-later
 */

export const ContentPageDynamicContentElement = {
  methods: {
    getComponent (element) {
      const elementType = element.type.substring(0, 1).toUpperCase() + element.type.substring(1).toLowerCase();
      let elementBundle = element.bundle.split("_"),
        elementName = "";

      for (const index in elementBundle) {
        elementBundle[index] = elementBundle[index].substring(0, 1).toUpperCase() + elementBundle[index].substring(1).toLowerCase();
      }
      elementBundle = elementBundle.join("");
      elementName = "ContentPage" + elementType + elementBundle;
      return elementName;
    }
  }
};
