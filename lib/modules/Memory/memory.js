if (typeof charts == "undefined") {
	var charts = {};
}

charts.memory_usage = 
{

	// will hold the chart object
	plot: null,

	// will hold the data object
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
        	grow: {active:false},
            lines: {
        		show: true,
        		fill: true,
        		lineWidth: 2,   // for poofyer lines
        		//lineWidth: false,
        		steps: false
        	},
            points: {show:false}
        },
        legend: { position: "ne", backgroundColor: "#000", backgroundOpacity: .4 },
        yaxis: {
		labelWidth: 40,
        	tickDecimals:1,
        	tickFormatter: function(val,axis){
		         return parseFloat(val).toFixed(axis.tickDecimals);
		      }
        },
        // xaxis: {mode: 'time', timezone: "browser", minTickSize: [10, "minute"]},
        //xaxis: {mode: 'time', minTickSize: ["1", "hour"], timeformat: "%H", min: today_min, max: today_max, ticks: 12},
		xaxis: {mode: 'time', timezone: null,  minTickSize: ["5", "minute"], timeformat: "%H",  min: today_min, max: today_max, ticks: 12 },
        
        colors: [],
        shadowSize:1,
        tooltip: true,
		tooltipOpts: {

			content: function(label, xval, yval, flotItem) {
				return label + " : " + parseFloat(yval).toFixed(2);
	    	},

			//content: "%s : %y.2",
			shifts: {
				x: 10,
				y: -20
			},
			dateFormat: "%y-%0m-%0d",
			defaultTheme: false
		}
	},

	setData: function($data,$info)
	{

		this.$data = $data;

		this.options.yaxis.min = $info.ymin;
		this.options.yaxis.max = $info.ymax;

		this.chartwidth = $info.chartwidth;
		this.chartheight = $info.chartheight;

		//set timezone
		this.setTimeZone($info.timezonemode,$info.timezone);


	},

	setTimeZone: function ($mode,$zone){

		console.log ("module passed timezone mode : ", $mode );
		console.log ("module passed timezone : ", $zone );

		if ( $mode == "Browser")
			$newmode = "browser";
		else
			$newmode = "UTC";

		//this.options.xaxis.timezone = "UTC";
		//this.options.xaxis.timezone = "browser";
		this.options.xaxis.timezone = $newmode;

	},

	//dirty hack to override label for missing logs
	setLabel: function($data)
	{
		this.label = $data;		
	},

	//used to override date ranges needs cleaning up
	setRange: function($xmin,$xmax,$ticks)
	{
		this.options.xaxis.min   = $xmin;
		this.options.xaxis.max   = $xmax;
		this.options.xaxis.ticks = $ticks;

		//set the chart bar widthh
		this.setChartBarWidth($ticks);

  		//console.log(  $ticks, "setRange in CPU");

	},

	//used to override date ranges needs cleaning up
	setChartBarWidth: function($data)
	{

		//set the chart bar width
		switch ($data) {

			case 12 : 	//optimal for 24 hour view (6 tics )
						this.chartBarWidth       = 1*1*1000;
						break;

			case 6 : 	//optimal for 12 hour view (6 tics )
						this.chartBarWidth       = 60*1*1000;
						break; 

			case 3 :    //optimal for 6 hour view (3 ticks) 
						this.chartBarWidth       = 60*3*1000;
						break;

		};

	},


	//generate chart datasets and visual styles here
	genChart: function($chartdata)
	{

		//dirty hack we will store this soon
		var myTicks = this.options.xaxis.ticks;

		//primary GREEN chart - we change this based on if the view is 24 hours (line chart) or less (bar chart)
		if (this.$data[0]) { 
			if ( myTicks  == 12 ) {
				$chartdata[0] = [ {	label: this.$data[0].label, data: this.$data[0].data,
					     			lines: {show:true, fillColor: "#8ec657"},
					     			points: {fillColor: "#88bbc8"}, 
					     			color:"#8ec657"
				} ];
			} else {
				$chartdata[0] = [	{	label: this.$data[0].label, data: this.$data[0].data,
					     			lines: {show: false},
					     			//bars: { show: true, barWidth: .9, align: "center", fill: 1, fillColor: "#8ec657"},  		        			
					     			bars: { show: true, barWidth: this.chartBarWidth, align: "center", fill: 1, fillColor: "#8ec657"},  		        			
					        		color: "#8ec657"
				} ];	
			};
		};

		//secondary is for YELLOW overload 
		if (this.$data[1] ) { 
			$chartdata[1] = [	{	label: this.$data[1].label, data: this.$data[1].data,
				     			lines: {show: false},
				     			bars: { show: true, barWidth: this.chartBarWidth, align: "center", fill: 1, fillColor: "#ebc824" },  
				        		color: "#ebc824"
			} ];
		};


		//thrid RED swap line is for swap data!
		if (this.$data[2]) { 
			$chartdata[2] = [	{	label: this.$data[2].label, data: this.$data[2].data,
				     			lines: {show: true, fill: false, fillColor: "#88bbc8"},
								points: { fill: false, fillColor: "#c65757" },
				        		color: "#c65757"
			} ];
		};

	},


	// initialize
	init: function($divId)
	{
		// apply styling
		this.utility.applyStyle(this);
		this.options.legend.container = "#"+$divId+"_legend";
		this.options.legend.noColumns = 3;
		
		var chartWidth = this.chartwidth;
		if (chartWidth == 0 || !chartWidth)
			chartWidth = 530;

		var p = $('#' + $divId);
		p.width( chartWidth );
		
		//generate chart datasets
		var chartDataset = [];

		this.genChart(chartDataset);

		// make chart
		if ($.isArray(this.$data))
		{
			//memory, overload and swap
			if (this.$data[0]  && this.$data[1] && this.$data[2] ) { 
				this.plot = $.plot( '#' + $divId, chartDataset[0].concat(chartDataset[1],chartDataset[2] ), this.options);
				//console.log( 'memory data and swap and overload' );

			}

			//memory and swap (skip overload)
			else if (this.$data[0] && this.$data[1] ) { 
				this.plot = $.plot( '#' + $divId, chartDataset[0].concat(chartDataset[1] ), this.options);
				//console.log( 'memory data and overload' );

			}

			//memory and swap (skip overload)
			else if (this.$data[0] && this.$data[2] ) { 
				this.plot = $.plot( '#' + $divId, chartDataset[0].concat(chartDataset[2] ), this.options);
				//console.log( 'memory data and swap' );

			}
			  //just memory alone			
			else if (this.$data[0] ) { 
				this.plot = $.plot( '#' + $divId, chartDataset[0], this.options);
				//console.log( 'memory data only' );

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
