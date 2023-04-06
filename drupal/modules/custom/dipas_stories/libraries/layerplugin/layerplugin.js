(function (Vue, $, Drupal, drupalSettings, window) {

  'use strict';

  Drupal.behaviors.layerplugin = {
    attach: function (context) {
      Vue.component(
        'layer',
        {
          props: {
            fieldname: {
              type: String,
              required: true
            },
            layer: {
              type: Object,
              required: true
            },
            isNew: {
              type: Boolean,
              required: false,
              default: false
            },
            overrides: {
              type: Object,
              required: false,
              default () {
                return {};
              }
            },
            initiallyVisible: {
              type: Boolean,
              required: false,
              default: false
            },
            widgetMode: {
              type: String,
              required: false,
              default: 'story'
            }
          },
          data () {
            return {
              detailsOpen: false,
              visible: false,
              layerOverrides: {
                layerName: "",
                layerJson: ""
              }
            };
          },
          computed: {
            layerTitle () {
              return this.layerOverrides.layerName.length
                ? this.layerOverrides.layerName + ' (Original: ' + this.layer.label.trim() + ')'
                : this.layer.label.trim();
            },
            inputTitle () {
              return Drupal.t(
                this.widgetMode === 'story'
                  ? 'The values entered here will be applied to all story steps'
                  : 'It is not possible to edit these values here',
                {},
                {'context': 'dipas_stories'}
              );
            }
          },
          mounted () {
            this.visible = this.initiallyVisible;
            Object.entries(this.overrides).forEach(([property, value]) => {
              this.layerOverrides[property] = value;
            });
          },
          watch: {
            layerOverrides: {
              deep: true,
              handler (val) {
                this.$emit('layerOverrides', this.layer.id, val)
              }
            }
          },
          template: `
            <div class="layer">
              <div class="inner">
                <span
                  v-if="widgetMode === 'story' || isNew"
                  class="remove"
                  @click="$emit('removeLayer', layer)"
                >
                  X
                </span>

                <label>
                  {{ Drupal.t('Layer', {}, {'context': 'dipas_stories'}) }}:
                </label>
                
                {{ layerTitle }}

                <label>
                  <input
                    v-model="visible"
                    type="checkbox"
                    class="visibilityToggle"
                    :data-fieldname="fieldname"
                    @click="$emit('toggleVisibility', layer, !visible)"
                  />

                  {{ Drupal.t(widgetMode === 'story' ? 'Initially visible' : 'Visible', {}, {'context': 'dipas_stories'}) }}
                </label>

                <div class="details">
                  <label
                    :class="{ opened: detailsOpen }"
                    @click="detailsOpen^=1"
                  >
                    {{ Drupal.t('Details', {}, {'context': 'dipas_stories'}) }}
                  </label>

                  <div
                    v-if="detailsOpen"
                    class="inputs"
                  >
                    <div>
                      <label>
                        {{ Drupal.t('Override layer name', {}, {'context': 'dipas_stories'}) }}:
                      </label>

                      <input
                        v-model="layerOverrides.layerName"
                        type="text"
                        :disabled="widgetMode !== 'story' && !isNew"
                        :title="inputTitle"
                      >
                    </div>

                    <div>
                      <label>
                        {{ Drupal.t('Layer-specific JSON settings', {}, {'context': 'dipas_stories'}) }}:
                      </label>

                      <textarea
                        v-model="layerOverrides.layerJson"
                        :disabled="widgetMode !== 'story' && !isNew"
                        :title="inputTitle"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `
        }
      );

      const layerWidget = {
        components: [
          'layer'
        ],
        data () {
          return {
            renderTimestamp: window.performance.now(),
            $selectedLayerContainer: null,
            $selectedLayers: null,
            $layerProperties: null,
            $layerSelection: null,
            $layerAdd: null,
            $visiblelayers: null,
            selectedLayers: [],
            visibleLayers: [],
            layerProperties: {},
            initiallySelectedLayers: []
          };
        },
        computed: {
          fieldname () {
            return $(this.$el.parentNode).attr('data-fieldname');
          },
          property () {
            return $(this.$el.parentNode).attr('data-property');
          },
          widgetSettings () {
            return drupalSettings.dipas_stories[this.fieldname];
          },
          layers: {
            get () {
              return this.selectedLayers.map(id => {
                return {
                  id,
                  label: $("option[value='" + id + "']", this.$layerSelection).text()
                };
              });
            },
            set (val) {
              this.selectedLayers = val;
              this.$selectedLayers.val(val.join('/'));
              this.$layerSelection.val(null).trigger('change');
            }
          },
          overrides () {
            return (id) => this.layerProperties[id] ? this.layerProperties[id] : {};
          }
        },
        mounted () {
          this.onMount();
        },
        methods: {
          onMount () {
            // Store instance-specific DOM node elements
            this.$selectedLayerContainer = $('div.selectedLayerContainer[data-fieldname="' + this.fieldname + '"][data-property="' + this.property + '"]');
            this.$selectedLayers = $('.selectedLayers[data-fieldname="' + this.fieldname + '"][data-property="' + this.property + '"]');
            this.$layerProperties = $('.layerProperties[data-fieldname="' + this.fieldname + '"][data-property="' + this.property + '"]');
            this.$layerSelection = $('.layerSelection[data-fieldname="' + this.fieldname + '"][data-property="' + this.property + '"]');
            this.$layerAdd = $('.layerAdd[data-fieldname="' + this.fieldname + '"][data-property="' + this.property + '"]');
            this.$visiblelayers = $('.visibleLayers[data-fieldname="' + this.fieldname + '"][data-property="' + this.property + '"]');

            // Initialize local data storage
            this.selectedLayers = this.$selectedLayers.val().split('/').filter(elem => elem.length);
            this.initiallySelectedLayers = JSON.parse(JSON.stringify(this.selectedLayers));
            if (this.$layerProperties.val()) {
              this.layerProperties = JSON.parse(this.$layerProperties.val());
            }
            this.visibleLayers = this.$visiblelayers.val().split('/').filter(elem => elem.length);

            // Initialize widget element behaviors
            this.$layerAdd.attr('disabled', 'disabled');
            this.$layerSelection.val(null).trigger('change');
            this.$layerSelection.on('change', this.onSelectionChange);
            this.$layerAdd.on('click', this.onLayerAdd);

            // Deactivate already configured options
            this.$nextTick(() => {
              this.initiallySelectedLayers.forEach(id => {
                this.toggleOptionStatus(id, false);
              });

              if (this.widgetSettings['widgetMode'] === 'story') {
                Sortable.create(
                  this.$selectedLayerContainer.get(0),
                  {
                    onEnd: this.onSortingEnd
                  }
                );
              }
            });
          },
          onSelectionChange (event) {
            if (event.target.value) {
              this.$layerAdd.removeAttr('disabled');
            }
            else {
              this.$layerAdd.attr('disabled', 'disabled');
            }
          },
          onLayerAdd (event) {
            event.preventDefault();
            event.stopPropagation();
            const id = this.$layerSelection.val();
            this.toggleOptionStatus(id, false);
            // Do NOT change this to .push(), since that would render the
            // computed property and value storage unfunctional!
            this.layers = [...this.layers.map(layer => layer.id), id];

            this.updateViewportMap();
          },
          onLayerRemove (layer) {
            let layers = this.layers;
            layers.splice(layers.findIndex(currentLayer => currentLayer.id === layer.id), 1);
            this.layers = layers.map(layer => layer.id);
            this.toggleOptionStatus(layer.id, true);
            delete this.layerProperties[layer.id];
            this.$layerProperties.val(JSON.stringify(this.layerProperties));

            this.onToggleVisibility(layer, false);
            this.updateViewportMap();
          },
          onToggleVisibility (layer, visible) {
            let visibleLayers = JSON.parse(JSON.stringify(this.visibleLayers));

            if (visible && visibleLayers.indexOf(layer.id) === -1) {
              visibleLayers.push(layer.id);
              visibleLayers.sort();
            }
            else if (!visible && visibleLayers.indexOf(layer.id) > -1) {
              visibleLayers.splice(visibleLayers.indexOf(layer.id), 1);
            }

            this.visibleLayers = visibleLayers;
            this.$visiblelayers.val(visibleLayers.join('/'));

            this.updateViewportMap();
          },
          onLayerOverrides (id, overrides) {
            this.layerProperties[id] = {};
            Object.entries(overrides).forEach(([property, value]) => {
              if (overrides.hasOwnProperty(property)) {
                this.layerProperties[id][property] = value;
              }
            });
            this.$layerProperties.val(JSON.stringify(this.layerProperties));
          },
          toggleOptionStatus (id, enabled) {
            if (enabled) {
              $("option[value='" + id + "']", this.$layerSelection).removeAttr('disabled');
            }
            else {
              $("option[value='" + id + "']", this.$layerSelection).attr('disabled', 'disabled');
            }
            this.$layerSelection.select2('destroy').select2();
          },
          updateViewportMap () {
            try {
              Drupal.MasterportalMapViewport.updateViewportMap(this.fieldname);
            } catch (e) {}
          },
          onSortingEnd () {
            const layerIds = [];

            $('div.layer', this.$selectedLayerContainer).each((index, layer) => {
              layerIds.push($(layer).attr('data-layerid'));
            });

            this.layers = JSON.parse(JSON.stringify(layerIds));
            this.renderTimestamp = window.performance.now();

            this.updateViewportMap();
          }
        },
        template: `
          <div 
            class="selectedLayerContainer"
            :class="{sortableList: widgetSettings['widgetMode'] === 'story'}"
            :data-fieldname="fieldname"
            :data-property="property"
          >
            <layer
              v-for="(layer, index) in layers"
              :key="'layer-' + index + '-' + renderTimestamp"
              :fieldname="fieldname"
              :layer="layer"
              :isNew="initiallySelectedLayers.indexOf(layer.id) === -1"
              :initiallyVisible="visibleLayers.indexOf(layer.id) > -1"
              :overrides="overrides(layer.id)"
              :widgetMode="widgetSettings['widgetMode']"
              :data-layerid="layer.id"
              @removeLayer="onLayerRemove"
              @toggleVisibility="onToggleVisibility"
              @layerOverrides="onLayerOverrides"
            />
          
            <p v-if="!layers.length">
              {{ Drupal.t('No layers defined', {}, {'context': 'dipas_stories'}) }}
            </p>
          </div>
        `
      };

      if (!this.initialized) {
        $('.selectedLayers').each((index, elem) => {
          let property = $(elem).attr('data-property'),
            fieldname = $(elem).attr('data-fieldname'),
            widgetElementSelector = '.layerContainer[data-fieldname="' + fieldname + '"][data-property="' + property + '"] > .widget',
            layerPropertyWidget = Object.assign(
              {},
              {
                el: widgetElementSelector,
                property,
                fieldname
              },
              layerWidget
            );

          new Vue(layerPropertyWidget);
        });

        $(document).on('select2:open', () => {
          document.querySelector('.select2-search__field').focus();
        });

        this.initialized = true;
      }
    }
  };

}(Vue, jQuery, Drupal, drupalSettings, window));
