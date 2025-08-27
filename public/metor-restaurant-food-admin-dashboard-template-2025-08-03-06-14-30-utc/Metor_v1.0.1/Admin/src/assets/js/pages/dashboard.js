
//
// sales_funnel
//
var options = {
    chart: {
        type: "area",
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    series: [
        {
            data: [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54],
        },
    ],
    stroke: {
        width: 0,
        curve: "smooth",
    },
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            opacityFrom: 0.7,
            opacityTo: 0,
            stops: [0, 100],
        },
    },

    markers: {
        size: 0,
    },
    colors: ["#4d5761"],
    tooltip: {
        fixed: {
            enabled: false,
        },
        x: {
            show: false,
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return "";
                },
            },
        },
        marker: {
            show: false,
        },
    },
};
var chart = new ApexCharts(document.querySelector("#sales_funnel"), options);
chart.render();

//
// order_funnel
//
var options = {
    chart: {
        type: "bar",
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    series: [
        {
            data: [17, 83, 56, 45, 29, 92, 38, 72, 11, 67, 53, 29, 92, 18, 16, 11, 8, 5, 25, 83, 56, 45, 72, 11, 67, 53, 29, 92],
        },
    ],
    stroke: {
        width: 0,
        curve: "smooth",
    },
    plotOptions: {
        bar: {
            columnWidth: "25%",
            dataLabels: {
                position: "top", // top, center, bottom
            },
        },
    },

    markers: {
        size: 0,
    },
    colors: ["#663ffa"],
    tooltip: {
        fixed: {
            enabled: false,
        },
        x: {
            show: false,
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return "";
                },
            },
        },
        marker: {
            show: false,
        },
    },
};
var chart = new ApexCharts(document.querySelector("#order_funnel"), options);
chart.render();

//
// customer_funnel
//
var options = {
    chart: {
        type: "bar",
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    series: [
        {
            data: [17, 83, 56, 45, 29, 92, 38, 72, 11, 67, 53, 29, 92, 18, 16, 11, 8, 5, 25, 83, 56, 45, 72, 11, 67, 53, 29, 92],
        },
    ],
    stroke: {
        width: 0,
        curve: "smooth",
    },
    plotOptions: {
        bar: {
            columnWidth: "25%",
            dataLabels: {
                position: "top", // top, center, bottom
            },
        },
    },

    markers: {
        size: 0,
    },
    colors: ["#663ffa"],
    tooltip: {
        fixed: {
            enabled: false,
        },
        x: {
            show: false,
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return "";
                },
            },
        },
        marker: {
            show: false,
        },
    },
};
var chart = new ApexCharts(document.querySelector("#customer_funnel"), options);
chart.render();

//
// cancel_funnel
//
var options = {
    chart: {
        type: "area",
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    series: [
        {
            data: [45, 12, 78, 31, 56, 89, 22, 67, 41, 53, 96],
        },
    ],
    stroke: {
        width: 0,
        curve: "smooth",
    },
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            opacityFrom: 0.7,
            opacityTo: 0,
            stops: [0, 100],
        },
    },

    markers: {
        size: 0,
    },
    colors: ["#4d5761"],
    tooltip: {
        fixed: {
            enabled: false,
        },
        x: {
            show: false,
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return "";
                },
            },
        },
        marker: {
            show: false,
        },
    },
};
var chart = new ApexCharts(document.querySelector("#cancel_funnel"), options);
chart.render();

//
// SEMI CIRCLE GAUGE
//
var options = {
    chart: {
        height: 262,
        type: "radialBar",
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 225,
            hollow: {
                margin: 0,
                size: "70%",
                background: "transparent",
                image: undefined,
                imageOffsetX: 0,
                imageOffsetY: 0,
                position: "front",
                dropShadow: {
                    enabled: true,
                    top: 3,
                    left: 0,
                    blur: 4,
                    opacity: 0.24,
                },
            },
            track: {
                background: "rgba(170,184,197, 0.4)",
                strokeWidth: "67%",
                margin: 0,
            },

            dataLabels: {
                showOn: "always",
                name: {
                    offsetY: -10,
                    show: true,
                    color: "#888",
                    fontSize: "17px",
                },
                value: {
                    formatter: function (val) {
                        return parseInt(val);
                    },
                    color: "#111",
                    fontSize: "36px",
                    show: true,
                },
            },
        },
    },
    fill: {
        type: "gradient",
        gradient: {
            shade: "dark",
            type: "horizontal",
            shadeIntensity: 0.5,
            gradientToColors: ["#663ffa"],
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [100, 100],
        },
    },
    series: [75],
    stroke: {
        lineCap: "round",
    },
    labels: ["Archieved"],
};
var chart = new ApexCharts(document.querySelector("#order_summary"), options);
chart.render();

//
// COLUMN CHART WITH DATALABELS
//
var options = {
    chart: {
        height: 269,
        type: "bar",
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        bar: {
            borderRadius: 2,
            columnWidth: "30%",
            dataLabels: {
                position: "top", // top, center, bottom
            },
        },
    },
    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return val + "+";
        },
        offsetY: -25,
        style: {
            fontSize: "12px",
            colors: ["#304758"],
        },
    },
    colors: ["#4d5761"],
    legend: {
        show: true,
        horizontalAlign: "center",
        offsetX: 0,
        offsetY: -5,
    },
    series: [
        {
            name: "Total Order",
            data: [37, 43, 40, 51, 45, 23, 54],
        },
    ],
    xaxis: {
        categories: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        position: "bottom",
        labels: {
            offsetY: 0,
        },
        axisBorder: {
            show: true,
        },
        axisTicks: {
            show: true,
        },

        tooltip: {
            enabled: true,
            offsetY: -10,
        },
    },

    yaxis: {
        axisBorder: {
            show: true,
        },
        axisTicks: {
            show: true,
        },
        labels: {
            show: true,
            formatter: function (val) {
                return val + "+";
            },
        },
    },
    grid: {
        row: {
            colors: ["transparent", "transparent"], // takes an array which will be repeated on columns
            opacity: 0.2,
        },
        borderColor: "#f1f3fa",
    },
};

var chart = new ApexCharts(
    document.querySelector("#datalabels-column2"),
    options
);

chart.render();



// Daily Deliverred Orders 

function generateData(count, yrange) {
    var i = 0;
    var series = [];
    while (i < count) {
        var x = (i + 1).toString();
        var y =
            Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;

        series.push({
            x: x,
            y: y,
        });
        i++;
    }
    return series;
}

var options = {
    chart: {
        toolbar: {
            show: false,
        },
        height: 269,
        type: "heatmap",
    },

    dataLabels: {
        enabled: false,
    },
    colors: ["#663ffa"],
    series: [
        {
            name: "Food",
            data: [
                {
                    x: "Sun",
                    y: 22,
                },
                {
                    x: "Mon",
                    y: 29,
                },
                {
                    x: "Tue",
                    y: 13,
                },
                {
                    x: "Wed",
                    y: 32,
                },

                {
                    x: "Thu",
                    y: 32,
                },
                {
                    x: "Fri",
                    y: 32,
                },
                {
                    x: "Sat",
                    y: 32,
                },
            ],
        },
        {
            name: "Beverage",
            data: generateData(7, {
                min: 0,
                max: 90,
            }),
        },
        {
            name: "Snack",
            data: generateData(7, {
                min: 0,
                max: 90,
            }),
        },
        {
            name: "Hot Drinks",
            data: generateData(7, {
                min: 0,
                max: 90,
            }),
        },
        {
            name: "Veg Food",
            data: generateData(7, {
                min: 0,
                max: 90,
            }),
        },
    ],

    xaxis: {
        type: "category",
    },
    yaxis: {
        labels: {
            style: {
                fontSize: "11px", // Increase font size for y-axis labels
            },
        },
    },
};

var chart = new ApexCharts(document.querySelector("#basic-heatmap"), options);

chart.render();
