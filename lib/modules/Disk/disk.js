if (typeof charts == "undefined") {
	var charts = {};
}


charts.disk_usage = 
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
        		lineWidth: false,
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

			shifts: {
				x: 10,
				y: -20
			},
			//dateFormat: "%y-%0m-%0d",
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

  		console.log(  $data3, "setRange in Disk!");

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
		


		//secondary YELLOW overload
		var loadAchart = [];

		if (this.$data[0]) { 
			loadAchart = [	{	label: this.$data[0].label,  data: this.$data[0].data,
	     						lines: {show:true, fillColor: "#8ec657"},
	     						points: {fillColor: "#88bbc8"},
	     						color: "#8ec657"
			} ];
		};

		//secondary YELLOW overload
		var loadBchart = [];

		if (this.$data[1]) { 
			loadBchart = [	{	label: this.$data[1].label,  data: this.$data[1].data,
	     						lines: {show: false},
	     						bars: { show: true, barWidth: this.chartBarWidth, fill: 1, fillColor: "#ebc824"},
	     						color: "#ebc824"

			} ];
		};


		//generate charts
		load2charts = loadAchart.concat(loadBchart );
		load1charts = loadAchart;


		//if its not an array then there is no chart data i presume
		if ($.isArray(this.$data))
		{
			// data 2 is supposed to be the secodary overload
			if (this.$data[1]) { 
				this.plot = $.plot('#' + $divId, load2charts, this.options);
				console.log( 'disk level 2' );

			} else if (this.$data[0])  {
				this.plot = $.plot('#' + $divId, load1charts, this.options);
				console.log( 'disk level 1' );

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
