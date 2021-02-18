/**
 * @license GPL-2.0-or-later
 */

<script>
import Chart from "chart.js";
export default {
  name: "StatisticsDonut",
  props: {
    headline: {
      type: String,
      default: ""
    },
    labels: {
      type: Array,
      default () {
        return [];
      }
    },
    colors: {
      type: Array,
      default () {
        return [0, 0, 0, 0];
      }
    },
    donutData: {
      type: Array,
      default () {
        return [];
      }
    },
    keyId: {
      type: String,
      default: ""
    },
    showOverlay: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      chartJsOptions: {
        type: "doughnut",
        data: {
          labels: this.labels,
          datasets: [
            {
              data: this.donutData,
              backgroundColor: this.colors,
              borderWidth: 2
            }
          ]
        },
        options: {
          responsive: true,
          legend: {
            display: false
          },
          cutoutPercentage: 80,
          tooltips: {
            callbacks: {
              label: function (tooltipItem, data) {
                const dataset = data.datasets[tooltipItem.datasetIndex],
                  sum = dataset.data.reduce(function (total, currentValue) {
                    return total + currentValue;
                  }),
                  currentValue = dataset.data[tooltipItem.index],
                  percentage = parseFloat((currentValue / sum * 100).toFixed(1));

                return data.labels[tooltipItem.index] + ": " + percentage + "%";
              }
            }
          }
        }
      },
      showModal: false
    };
  },
  mounted () {
    this.initChart(this.keyId, this.chartJsOptions);
  },
  methods: {
    initChart (chartId, chartData) {
      const ctx = document.getElementById(chartId),

        chart = new Chart(ctx, {
          type: chartData.type,
          data: chartData.data,
          options: chartData.options
        });

      return chart;
    },
    cancel: function () {
      this.showModal = false;
    },
    openModal: function () {
      this.showModal = true;
    },
    drawChartModal: function () {
      this.initChart(this.keyId + "Modal", this.chartJsOptions);
    }
  }
};
</script>

<template>
  <div>
    <div
      class="chart"
      @click="openModal"
    >
      <p class="headline">
        {{ headline }}
      </p>
      <canvas
        :id="keyId"
        height="220px"
      />
    </div>
    <ModalElement
      v-if="showModal && showOverlay"
      @closeModal="cancel"
      @hook:mounted="drawChartModal"
    >
      <div class="chart">
        <p class="headline">
          {{ headline }}
        </p>
        <canvas
          :id="keyId + 'Modal'"
          height="300px"
        />
      </div>
    </ModalElement>
  </div>
</template>

<style>
  div .graphModal {
    width: 400px;
    height: 400px;
  }
</style>
