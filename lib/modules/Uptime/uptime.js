if (typeof charts == "undefined") {
	var charts = {};
}


charts.uptime_usage = 
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
          	},

			font: { size:10,  color: "grey",  variant:"small-caps" },
			ticks:4
        },
		xaxis: {mode: 'time',

			font: { size:10,  color: "grey",  variant:"small-caps" },
			timezone: null,  minTickSize: ["5", "minute"], timeformat: "%H",  min: today_min, max: today_max, 
			ticks: 12,

			tickFormatter: function(v,axis){

		   		var date = new Date(v);
		 
		        if (date.getSeconds() % 20 == 0) {
		            var hours = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
		            var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
		            var seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
		 
		            //return hours + ":" + minutes + ":" + seconds;
		            if ( hours == "00" || hours == "12") {
    					return "<span style='color: #bbb'>" +  hours  + "</span>"; 
    				} else {
    					return "<span style='color: grey'>" +  hours  + "</span>"; 
    				}

		        } else {
		            return "";
		        }

		    }
		    
		},
        colors: [],
        shadowSize:1,

        tooltip: true,
		tooltipOpts: {

			content: function(label, xval, yval, flotItem) {
				var itemTime = new Date(xval);
				itemTime = this.getItemTime(itemTime);
				return label + " : " + parseFloat(yval).toFixed(2) + " / " + itemTime ;		
	    	},

			shifts: {
				x: 10,
				y: -20
			},
			//dateFormat: "%y-%0m-%0d",
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

		//console.log (">> in uptime.js <<");

		//set timezone
		this.setTimeZone($info.timezonemode,$info.timezone);

		//get first and last value from dataset
		/*
		first = this.$data[0].data[0];
		last = this.$data[0].data.pop();

		formattedTime = this.timeConverter(first[0] / 1000);
		console.log ("first time in dataset", first[0] + " " + formattedTime );
		//this.options.xaxis.min = first[0];

		formattedTime = this.timeConverter(last[0] / 1000 );
		console.log ("last time in dataset", last[0] + " " + formattedTime );
		//this.options.xaxis.max = last[0];
		*/
	},

	setTimeZone: function ($mode,$zone){

		//console.log ("module passed timezone mode : ", $mode );
		//console.log ("module passed timezone : ", $zone );

		if ( $mode == "Browser")
			$newmode = "browser";
		else if ($mode == "UTC")
			$newmode = "UTC";
		else if ($mode == "Override")
			$newmode = $zone;

		this.options.xaxis.timezone = $newmode;

	},

	timeConverter: function (UNIX_timestamp){

		var dt = new Date([UNIX_timestamp] * 1000);

		data = (dt.getHours() + ':' + dt.getMinutes() + ':' + dt.getSeconds() + ' -- ' + dt );

		return data;
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

		//dirty hack we will send this over from calling php soon
		var myTicks = this.options.xaxis.ticks;


		//secondary YELLOW overload
		if (this.$data[0]) { 
			$chartdata[0] = [	{	label: this.$data[0].label,  data: this.$data[0].data,
	     						lines: {show:true, fillColor: "#8ec657"},
	     						points: {fillColor: "#88bbc8"},
	     						color: "#8ec657"
			} ];
		};

		if (this.$data[1]) { 
			$chartdata[1] = [	{	label: this.$data[1].label,  data: this.$data[1].data,
	     						lines: {show: false},
	     						bars: { show: true, barWidth: this.chartBarWidth, fill: 1, fillColor: "#ebc824"},
	     						color: "#ebc824"

			} ];
		};


	},



	// initialize and render charts
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

		//if its not an array then there is no chart data i presume
		if ($.isArray(this.$data))
		{
			// data 2 is supposed to be the secodary overload
			if ( this.$data[0]  && this.$data[1]) { 
				this.plot = $.plot('#' + $divId, chartDataset[0].concat(chartDataset[1] ), this.options);
				//console.log( 'uptime level 2' );

			} else if (this.$data[0])  {
				this.plot = $.plot('#' + $divId, chartDataset[0], this.options);
				//console.log( 'uptime level 1' );

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
