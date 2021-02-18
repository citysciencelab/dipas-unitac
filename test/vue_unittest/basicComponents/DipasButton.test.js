/**
 * @license GPL-2.0-or-later
 */

// Import Vue and the component being tested
import Vue from "vue";
import DipasButton from "../../../src/basicComponents/DipasButton.vue";


// Test the DipasButton component
describe("DipasButton", () => {

  it("set disabled text", () => {
    const Constructor = Vue.extend(DipasButton),
      DipasExtend = new Constructor({propsData: {
        "disabled": true,
        "disabledText": "Disabled"
      }}).$mount();

    expect(DipasExtend.titleText).to.equal("Disabled");
  });

  it("set disabled text to null", () => {
    const Constructor = Vue.extend(DipasButton),
      DipasExtend = new Constructor({propsData: {
        "disabled": false,
        "disabledText": "Disabled"
      }}).$mount();

    expect(DipasExtend.titleText).to.be.null;
  });

});
