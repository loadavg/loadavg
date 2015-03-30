
<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Charting core for LoadAvg included by charts.php
* used in main charts and override modules
*
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>


			<script type="text/javascript">
			(function () {

			    var options =  {
			    	
			        grid: {
			            show: true,
			            //color: "#efefef",
			            color: "#EEE",
			            axisMargin: 0,
			            borderWidth: 1,
			            hoverable: true,
			            autoHighlight: true,

					    aboveData: true,
					    //labelMargin: 5,
					    borderWidth: 1,
					    //minBorderMargin: 5 ,
					    clickable: true, 
					    mouseActiveRadius: 20,

			            borderColor: "#797979",
			            backgroundColor : "#353535"
			        },

			        series: {
			            bars: {
			                show: true,
			                barWidth: 2,
			                fillColor: {colors:[{opacity: 1},{opacity: 1}]},
			                align: "center"
			            },
			            color: "#26ADE4",
			            stack: 0
			        },
			        width: 1.5,

	        
			        xaxis: {

			        	font: { size:10,  color: "white",  variant:"small-caps" },

			        	show: true, 
			        	min: 0, max: 2,

						ticks: [ [1, "AVG"] ],
  						tickLength: 0,
						//alignTicksWithAxis: 1,
						color: "grey",
        				
        				axisLabel: "AVG",
        				axisLabelFontSizePixels: 8,
        				//axisLabelFontSize: 8,
      					axisLabelFontFamily: 'Verdana, Arial',
        				//axisLabelPadding: 3
                    
						
			        },
			       
			  /*   	
			        xaxis: {
			        	//show: true, 
			        	min: 1, max: 1,
        				tickLength: 0, // disable tick
						ticks: [[1, "AVG"]],

						font: { size:10,  color: "white",  variant:"small-caps", align: "center"
						       //style:"italic", weight:"bold", family:"sans-serif", 
						   },
			        },
				*/	

			        yaxis: {
			        	show: false, 
			        	max: <?php echo $chartData['chart']['ymax']; ?>, 
			        	min: <?php echo $chartData['chart']['ymin'];?>, 
			        	reserveSpace: false, 
			        	labelWidth: 15
			        },
			        legend: { 
			        	show: false 
			        },
					tooltip: true,

					tooltipOpts: {

						content: function(label, xval, yval, flotItem) {
							return "Avg " + parseFloat(yval).toFixed(4);
				    	},

						shifts: {
							x: 20,
							y: -20
						},
						defaultTheme: false
					} 

			     };
			
				$(function () {
                 
                	$("#minmax_<?php echo $chart->id; ?>").width(45).height(162);
                	$.plot($("#minmax_<?php echo $chart->id; ?>"),[[[1, <?php echo $chartData['chart']['mean']; ?>]]],options);

				})

			})();
			</script>
				<!--
                <div id="minmax_<?php echo $chart->id; ?>" style="width:45px; height:176px;top: 18px;right: 5px;"></div>
                
                <div id="minmax_<?php echo $chart->id; ?>" class="pull-right innerLR" style="right: 5px; top: 15px;"></div>
                -->
                <div id="minmax_<?php echo $chart->id; ?>" style="height: <?php echo $chartHeight;?>px; right: 5px; top: 12px;" class="chart-holder"></div>


