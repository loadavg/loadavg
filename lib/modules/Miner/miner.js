if (typeof charts == "undefined") {
	var charts = {};
}


charts.miner_load = 
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
        		//lineWidth: false,
        		lineWidth: 2,   // for poofyer lines
        		steps: false
        	},
            points: {show:false}
        },
        legend: { position: "nw", backgroundColor: "#000", backgroundOpacity: .4 },
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

			//content: "%s : re %y.2",
			content: function(label, xval, yval, flotItem){
				var itemTime = new Date(xval);
				itemTime = this.getItemTime(itemTime);
				return label + " : " + parseFloat(yval).toFixed(2) + " / " + itemTime ;		
	    	},

			shifts: {
				x: 10,
				y: -20
			},
			//precision: 2,
			dateFormat: "%y-%0m-%0d",
			defaultTheme: false
		}
	},

	//need to tweak this for timezones overrides ?





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

		if ( $mode == "Browser")
			$newmode = "browser";
		else if ($mode == "UTC")
			$newmode = "UTC";
		else if ($mode == "Override")
			$newmode = $zone;

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
					     			lines: {fillColor: "#8ec657"},
					     			lines: {show: true, fill: false, fillColor: "#88bbc8"},  
					     			color:"#8ec657"
				} ];
			} else {
				$chartdata[0] = [ {	label: this.$data[0].label, data: this.$data[0].data,
					     			lines: {show: false},
					     			bars: { show: true, barWidth: this.chartBarWidth, align: "center", fill: 1, fillColor: "#8ec657"},  		        			
					        		color: "#8ec657"
				} ];	
			};
		}

		//low hash white
		if (this.$data[1]) { 
			
				$chartdata[1] = [ {	label: this.$data[1].label, data: this.$data[1].data,
					     			lines: {show: false},
					     			points: { radius: 2, show: true, fill: true, fillColor: "#aaaaaa" },
					     			color:"#eeeeee"
				} ];

		}

		//high hash blue
		if (this.$data[2]) { 
			
				$chartdata[2] = [ {	label: this.$data[2].label, data: this.$data[2].data, 
					     			lines: {show: false},
					     			points: { radius: 2, show: true, fill: true, fillColor: "#058DC7" },
					     			color:"#26ADE4"
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
			if (this.$data[0] && this.$data[1] && this.$data[2]) { 
				this.plot = $.plot('#' + $divId, chartDataset[0].concat(chartDataset[1],chartDataset[2] ), this.options);
				//console.log( 'cpu level 3' );

			} else if (this.$data[0] && this.$data[1])  {
				this.plot = $.plot('#' + $divId, chartDataset[0].concat(chartDataset[1] ), this.options);
				//console.log( 'cpu level 2' );

			} else if (this.$data[0] && this.$data[2])  {
				this.plot = $.plot('#' + $divId, chartDataset[0].concat(chartDataset[2] ), this.options);
				//console.log( 'cpu level 2' );

			}  else if (this.$data[0])  {
				this.plot = $.plot('#' + $divId, chartDataset[0], this.options);
				//console.log( 'cpu level 1' );
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
