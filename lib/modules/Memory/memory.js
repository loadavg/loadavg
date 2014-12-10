if (typeof charts == "undefined") {
	var charts = {};
}

charts.memory_usage = 
{
	// chart data
	data: 
	{
		d1: []
	},

	// will hold the chart object
	plot: null,

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
        xaxis: {mode: 'time', minTickSize: ["1", "hour"], timeformat: "%H", min: today_min, max: today_max, ticks: 12},
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

  		//console.log(  $data3, "Logged in function!");

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
		
		//dirty hack we will store this soon
		var myTicks = this.options.xaxis.ticks;

		//primary GREEN chart - we change this based on if the view is 24 hours (line chart) or less (bar chart)
		var loadAchart = [];

		if ( myTicks  == 12 ) {
			loadAchart = [ {	label: this.$data[0].label, data: this.$data[0].data,
				     			lines: {show:true, fillColor: "#8ec657"},
				     			points: {fillColor: "#88bbc8"}, 
				     			color:"#8ec657"
			} ];
		} else {
			loadAchart = [	{	label: this.$data[0].label, data: this.$data[0].data,
				     			lines: {show: false},
				     			//bars: { show: true, barWidth: .9, align: "center", fill: 1, fillColor: "#8ec657"},  		        			
				     			bars: { show: true, barWidth: this.chartBarWidth, align: "center", fill: 1, fillColor: "#8ec657"},  		        			
				        		color: "#8ec657"
			} ];	
		}

		//secondary is for YELLOW overload 
		var loadBchart = [];

		if (this.$data[1] ) { 
			loadBchart = [	{	label: this.$data[1].label, data: this.$data[1].data,
				     			lines: {show: false},
				     			bars: { show: true, barWidth: this.chartBarWidth, align: "center", fill: 1, fillColor: "#ebc824" },  
				        		color: "#ebc824"
			} ];
		};


		//no secondary overloads here move on

		//thrid RED swap line is for swap data!
		var loadCchart = [];

		if (this.$data[2]) { 
			loadCchart = [	{	label: this.$data[2].label, data: this.$data[2].data,
				     			lines: {show: true, fill: false, fillColor: "#88bbc8"},
								points: { fill: false, fillColor: "#c65757" },
				        		color: "#c65757"
			} ];
		};

		//generate charts
		load3charts = loadAchart.concat(loadBchart, loadCchart);

		//needs to be different mate as its A+C but C = data 1 not 2
		load2charts = loadAchart.concat(loadCchart );

		load1charts = loadAchart;

		// make chart
		if ($.isArray(this.$data))
		{

			//memory, overload and swap
			if (this.$data[0]  && this.$data[1] && this.$data[2] ) { 
						this.plot = $.plot( '#' + $divId, load3charts, this.options);
			}

			//memory and swap (skip overload)
			else if (this.$data[0] && this.$data[2] ) { 
				this.plot = $.plot( '#' + $divId, load2charts, this.options);
			}
			   //just memory alone			
			else if (this.$data[0] ) { 
				this.plot = $.plot( '#' + $divId, load1charts, this.options);
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
