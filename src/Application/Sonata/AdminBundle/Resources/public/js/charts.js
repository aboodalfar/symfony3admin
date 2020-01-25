function drawBarsChart(chartBars, barsData, chartLabel) {
    $.plot(chartBars, [
        {data: barsData, bars: {show: true, fillColor: {colors: [{opacity: 1}, {opacity: 1}]}}, label: chartLabel}],
            {
                legend: {
                    backgroundColor: '#f6f6f6',
                    backgroundOpacity: 0.8
                },
                colors: ['#39d5db'],
                grid: {
                    borderColor: '#cccccc',
                    color: '#999999',
                    labelMargin: 10
                },
                yaxis: {
                    ticks: 5
                },
                xaxis: {
                    tickSize: 1
                }
            }
    );
}

function drawPieChart(pieChart, pieData) {
    $.plot(pieChart, pieData,
            {
                series: {
                    pie: {
                        show: true,
                        radius: 1,
                        label: {
                            show: true,
                            radius: 3 / 4,
                            formatter: function (label, pieSeries) {
                                return '<div class="chart-pie-label">' + label + '<br>' + Math.round(pieSeries.percent) + '%</div>';
                            },
                            background: {
                                opacity: 0.5,
                                color: '#000000'
                            }
                        }
                    }
                },
                grid: {
                    hoverable: true,
                    clickable: true
                },
                colors: ['#39a8db', '#db4a39', '#a8db39', '#39d5db'],
                legend: {
                    show: false
                }
            });
}



function drawChartClassic(chartClassic, classicData, chartLabel) {

    $.plot(chartClassic, [
        {data: classicData, lines: {show: true, fill: true, fillColor: {colors: [{opacity: 0.05}, {opacity: 0.05}]}}, points: {show: true}, label: chartLabel},
        {
            legend: {
                position: 'nw',
                backgroundColor: '#f6f6f6',
                backgroundOpacity: 0.8
            },
            colors: ['#555555', '#db4a39'],
            grid: {
                borderColor: '#cccccc',
                color: '#999999',
                labelMargin: 10,
                hoverable: true,
                clickable: true
            },
            yaxis: {
                ticks: 5
            },
            xaxis: {
                tickSize: 2
            }
        }]
            );

    // Creating and attaching a tooltip
    var previousPoint = null;
    chartClassic.bind("plothover", function (event, pos, item) {

        if (item) {
            if (previousPoint !== item.dataIndex) {
                previousPoint = item.dataIndex;

                $("#tooltip").remove();
                var x = item.datapoint[0],
                        y = item.datapoint[1];
                $('<div id="tooltip" class="chart-tooltip"><strong>' + y + '</strong> visits</div>')
                        .css({top: item.pageY - 30, left: item.pageX + 5})
                        .appendTo("body")
                        .show();
            }
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });

}
try {
    function drawChart(pieChart, pieData) {
        var data = google.visualization.arrayToDataTable(pieData);

        var options = {
            legend: 'none',
            pieSliceText: 'label',
        };

        var chart = new google.visualization.PieChart(document.getElementById(pieChart));

        chart.draw(data, options);
    }
}
catch (ex) {
    console.warn('charts error');
}
function drawChart2(pieChart, pieData) {
    var data = google.visualization.arrayToDataTable(pieData);

    var options = {
        legend: 'none',
        pieSliceText: 'label',
        is3D: true
    };

    var chart = new google.visualization.PieChart(document.getElementById(pieChart));

    chart.draw(data, options);
}

function drawChart3(pieChart, pieData) {
    var data = google.visualization.arrayToDataTable(pieData);

    var options = {
        legend: 'none',
        pieSliceText: 'label',
        pieHole: 0.4
    };

    var chart = new google.visualization.PieChart(document.getElementById(pieChart));

    chart.draw(data, options);
}

function drawChart4(pieChart, pieData) {
    var data = google.visualization.arrayToDataTable(pieData);

    var options = {
        legend: 'none',
        pieSliceText: 'label',
        pieHole: 0.4
    };

    var chart = new google.visualization.BarChart(document.getElementById(pieChart));

    chart.draw(data, options);
}

function drawChart5(pieChart, pieData) {
    var data = google.visualization.arrayToDataTable(pieData);

    var options = {
        legend: 'none',
        pieSliceText: 'label',
        pieHole: 0.5,
        colors: ['#FEB348','#4C9CDF','#97C763','#4E5158']
    };

    var chart = new google.visualization.ColumnChart(document.getElementById(pieChart));

    chart.draw(data, options);
}

function drawChart6(pieChart, pieData) {
    var data = google.visualization.arrayToDataTable(pieData);

    var options = {
        hAxis: {
            title: 'Time',
            logScale: true
        },
        vAxis: {
            title: 'Registered users',
            logScale: false
        },
        colors: ['#a52714', '#097138']
    };

    var chart = new google.visualization.LineChart(document.getElementById(pieChart));

    chart.draw(data, options);
}                                    