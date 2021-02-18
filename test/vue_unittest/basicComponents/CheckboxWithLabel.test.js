/**
 * @license GPL-2.0-or-later
 */

// Import Vue and the component being tested
import Vue from "vue";
import CheckBox from "../../../src/basicComponents/CheckboxWithLabel.vue";


// Test the DipasButton component
describe("CheckboxWithLabel", () => {

  it("set checked", () => {
    const Constructor = Vue.extend(CheckBox),
      CheckBoxExtend = new Constructor({propsData: {
        "checked": true,
        "value": true,
        "label": "Testing"
      }}).$mount();

    expect(CheckBoxExtend.internalValue).to.be.true;
  });

  it("set unchecked", () => {
    const Constructor = Vue.extend(CheckBox),
      CheckBoxExtend = new Constructor({propsData: {
        "checked": false,
        "value": false,
        "label": "Testing"
      }}).$mount();

    expect(CheckBoxExtend.internalValue).to.be.false;
  });

  it("set unique Id", () => {
    const Constructor = Vue.extend(CheckBox),
      CheckBoxExtend = new Constructor({propsData: {
        "checked": false,
        "value": false,
        "label": "Testing"
      }}).$mount();

    expect(CheckBoxExtend.uniqueId).to.be.an("string");
    expect(CheckBoxExtend.uniqueId.substr(0, 3)).to.equal("cb-");
  });

  it("set label style", () => {
    const Constructor = Vue.extend(CheckBox),
      CheckBoxExtend = new Constructor({propsData: {
        "checked": false,
        "value": false,
        "label": "Testing",
        "icon": "add_icon.jpg"
      }}).$mount();

    expect(CheckBoxExtend.labelStyle).to.equal("background-image: url(add_icon.jpg)");
  });


  it("set label style to ''", () => {
    const Constructor = Vue.extend(CheckBox),
      CheckBoxExtend = new Constructor({propsData: {
        "checked": false,
        "value": false,
        "label": "Testing"
      }}).$mount();

    expect(CheckBoxExtend.labelStyle).to.equal("");
  });

});
