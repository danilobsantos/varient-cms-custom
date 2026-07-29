/**
 * VrCharts Module
 */
var VrCharts = function () {

    // Ensure config exists
    if (typeof VrChartsConfig === 'undefined') {
        console.error('VrChartsConfig is not defined.');
        return;
    }

    // Configuration
    const chartLabels = VrChartsConfig.data.labels;
    const chartViewsData = VrChartsConfig.data.views;
    const chartEarningsData = VrChartsConfig.data.earnings;

    // Post Data
    const chartPostData = VrChartsConfig.data.posts || {active: 0, scheduled: 0, pending: 0};

    const currencySymbol = VrChartsConfig.settings.currencySymbol;
    const texts = VrChartsConfig.texts;

    // Helper: Get CSS variable
    const getCssVar = (name) => {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    };

    // Chart: Pageviews (Area Style)
    var initChartPageviews = function () {
        var element = document.getElementById('chartPageviews');
        if (!element) return;

        var borderColor = getCssVar('--bs-border-dashed-color');
        var baseColor = getCssVar('--bs-primary');
        var labelColor = getCssVar('--bs-gray-500');

        var options = {
            series: [{
                name: texts.pageviews.replace(/['"]+/g, ''),
                data: chartViewsData
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: 310,
                toolbar: {show: false},
                animations: {
                    enabled: false,
                }
            },
            dataLabels: {enabled: false},
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: [baseColor]
            },
            xaxis: {
                categories: chartLabels,
                tickAmount: 10,
                axisBorder: {show: false},
                axisTicks: {show: false},
                labels: {
                    hideOverlappingLabels: true,
                    style: {colors: labelColor, fontSize: '12px'},
                    formatter: function (val) {
                        if (typeof val === 'string') {
                            var parts = val.split('-');
                            if (parts.length === 3) return parts[2];
                        }
                        return val;
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {colors: labelColor, fontSize: '12px'},
                    formatter: function (val) {
                        return parseInt(val);
                    }
                }
            },
            tooltip: {
                style: {fontSize: '12px'},
                x: {
                    formatter: function (val, opts) {
                        if (opts && opts.dataPointIndex !== undefined && chartLabels[opts.dataPointIndex]) {
                            return chartLabels[opts.dataPointIndex];
                        }
                        return val;
                    }
                }
            },
            colors: [baseColor],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                yaxis: {lines: {show: true}}
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
    }

    // Chart: Earnings (Area Style)
    var initChartEarnings = function () {
        var element = document.getElementById('chartEarnings');
        if (!element) return;

        var borderColor = getCssVar('--bs-border-dashed-color');
        var baseColor = getCssVar('--bs-success');
        var labelColor = getCssVar('--bs-gray-500');

        var options = {
            series: [{
                name: texts.earnings.replace(/['"]+/g, ''),
                data: chartEarningsData
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: 310,
                toolbar: {show: false},
                animations: {
                    enabled: false
                }
            },
            legend: {show: false},
            dataLabels: {enabled: false},
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: [baseColor]
            },
            xaxis: {
                categories: chartLabels,
                tickAmount: 10,
                axisBorder: {show: false},
                axisTicks: {show: false},
                labels: {
                    hideOverlappingLabels: true,
                    style: {colors: labelColor, fontSize: '12px'},
                    formatter: function (val) {
                        if (typeof val === 'string') {
                            var parts = val.split('-');
                            if (parts.length === 3) return parts[2];
                        }
                        return val;
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {colors: labelColor, fontSize: '12px'},
                    formatter: function (val) {
                        return currencySymbol + Number(val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 5});
                    }
                }
            },
            tooltip: {
                style: {fontSize: '12px'},
                y: {
                    formatter: function (val) {
                        return currencySymbol + Number(val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 5});
                    }
                }
            },
            colors: [baseColor],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                yaxis: {lines: {show: true}}
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
    }

    // Public Methods
    return {
        init: function () {
            if (VrChartsConfig.charts.showPageviews) initChartPageviews();
            if (VrChartsConfig.charts.showEarnings) initChartEarnings();
        }
    }
}();

document.addEventListener('DOMContentLoaded', function () {
    VrCharts.init();

    // Redraw charts on tab changes to prevent rendering issues in hidden elements
    var chartTabs = [].slice.call(document.querySelectorAll('a[data-bs-toggle="tab"]'));
    chartTabs.forEach(function (triggerEl) {
        triggerEl.addEventListener('shown.bs.tab', function (event) {
            setTimeout(function () {
                window.dispatchEvent(new Event('resize'));
            }, 100);
        });
    });
});