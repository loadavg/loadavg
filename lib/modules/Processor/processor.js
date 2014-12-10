if (typeof charts == "undefined") {
	var charts = {};
}

charts.processor_load = 
{
	// will hold the chart object
	plot: null,

	// chart data
	$data: null,

	//we can change this when rendering different views
	chartBarWidth: 1*1*1000,
	
	// chart options
	options: 
	{		
		grid: {
			show: true,
		    aboveData: true,
		    color: "#3f3f3f",
		    labelMargin: 5,
		    axisMargin: 0, 
		    borderWidth: 1,
		    borderColor: null,
		    minBorderMargin: 5 ,
		    clickable: true, 
		    hoverable: true,
		    autoHighlight: true,
		    mouseActiveRadius: 20,
		    backgroundColor : { }
		},
        series: {
            bars: {
                show: true,
                fillColor: {colors:[{opacity: 1},{opacity: 1}]},
                align: "center"
            },
            color: "#26ADE4",
            stack: 0

        },
        legend: { position: "nw", backgroundColor: "#000", backgroundOpacity: .4 },
        yaxis: {
            labelWidth: 40,
            tickDecimals:1,
            tickFormatter: function(val,axis){
         		return parseFloat(val).toFixed(axis.tickDecimals);
          	}
        },
		 xaxis: {mode: 'time', minTickSize: ["5", "minute"], timeformat: "%H", min: today_min, max: today_max, ticks: 12},
		 //xaxis: {mode: 'time', minTickSize: ["5", "minute"], timeformat: "%H", min: today_min, max: today_max, ticks: 12},
         //xaxis: {mode: 'time', minTickSize: [2, "hour"]},
        colors: [],
        shadowSize:1,
        tooltip: true,

		tooltipOpts: {

			//content: "%s : re %y.2",
			content: function(label, xval, yval, flotItem){
					return "CPU : " + parseFloat(yval).toFixed(2);

				//return "Load:" + yval;
			   	//return "%s : %y ";
				/*
			        var yAxis = plot.getYAxes();
			        var range = (yAxis.max - yAxis.min);

			        if (range > 0.01) {
			          // range is larger than a year, just show year
			          return $.plot.formatDate(new Date(yval), '%Y');
			        }
			    */
	    	},

			shifts: {
				x: 10,
				y: -20
			},
			precision: 2,
			dateFormat: "%y-%0m-%0d",
			defaultTheme: false
		},
	},

	setData: function($data)
	{

			this.options.yaxis.min = $data[0].ymin;
			this.options.yaxis.max = $data[0].ymax;
			this.$data = $data;
			this.label = $data[0].label;

	},

	//dirty hack to override label for missing logs
	setLabel: function($data)
	{
		this.label = $data;		
	},

	//used to override date ranges needs cleaning up
	setRange: function($data1,$data2,$data3)
	{
		this.options.xaxis.min   = $data1;
		this.options.xaxis.max   = $data2;
		this.options.xaxis.ticks = $data3;

		//optimal for 12 hour view (6 tics )
		if ( $data3 == 6 ) {
		this.chartBarWidth       = 60*1*1000;
		};

		//optimal for 6 hour view (3 ticks) 
		if ( $data3 == 3 ) {
		this.chartBarWidth       = 60*3*1000;
		};

	},
	
	// initialize
	init: function($divId)
	{

		// apply styling
		this.utility.applyStyle(this);
		this.options.legend.container = "#"+$divId+"_legend";
		this.options.legend.noColumns = 3;
		
		var p = $('#' + $divId);
		p.width('530');
		//p.text('WHASSABI');
		
		// make chart
		//
		// data 0 regular load
		// data 1 overload
		// data 2 secondary overload

		if ($.isArray(this.$data))
		{
			// data 2 is supposed to be the secodary overload
			if (this.$data[2]) { 
				this.plot = $.plot(
					'#' + $divId, 
					[{
		     			label: this.$data[0].label,  data: this.$data[0].data,
		     			bars: {
		        			show: true,
		        			barWidth: this.chartBarWidth, 
                			align: "center", fill: 1, fillColor: "#8ec657"
						}, 
		     			points: {fillColor: "#88bbc8"}, 
		     			color:"#8ec657"
		     		},{
		     			label: this.$data[1].label,  data: this.$data[1].data,
		     			bars: {
		        			show: true,
		        			barWidth: this.chartBarWidth, 
                			align: "center", fill: 1, fillColor: "#ebc824"
						},  
		     			points: {fillColor: "#ebc824"}, 
		        		color: "#ebc824"
		     		},{
		     			label: this.$data[2].label,  data: this.$data[2].data,
		     			bars: {
		        			show: true,
		        			barWidth: this.chartBarWidth, 
                			align: "center", fill: 1, fillColor: "#c65757"
		                },
		     			points: {fillColor: "#c65757"}, 
		        		color: "#c65757"
		     		}], 
		     		this.options);
			} 
			
		}
 
	},

	utility:
	{
		chartColors: [ "#797979", "#444", "#777", "#999", "#797979", "#EEE" ],
		chartBackgroundColors: ["#353535", "#353535"],

		applyStyle: function(that)
		{
			that.options.colors = that.utility.chartColors;
			that.options.grid.backgroundColor = { colors: that.utility.chartBackgroundColors };
			that.options.grid.borderColor = that.utility.chartColors[4];
			that.options.grid.color = that.utility.chartColors[5];
		}
	}
}
