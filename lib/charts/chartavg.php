
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
			            color: "#efefef",
			            axisMargin: 0,
			            borderWidth: 1,
			            hoverable: true,
			            autoHighlight: true,
			            borderColor: "#797979",
			            backgroundColor : "#353535"
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
			        width: 0.5,
			        xaxis: {
			        	show: false, 
			        	min: 1
			        },
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
                 
                	$("#minmax_<?php echo $chart->id; ?>").width(35).height(140);
                	$.plot($("#minmax_<?php echo $chart->id; ?>"),[[[1, <?php echo $chartData['chart']['mean']; ?>]]],options);

				})

			})();
			</script>

            <td class="span1 hidden-phone" style="height: 170px">
                <div id="minmax_<?php echo $chart->id; ?>" style="width:35px;height:140px;top: 18px;right: 5px;"></div>
                <div style="position: relative; top: 13px;font-size: 11px;left: 3px;">Avg</div>
        	</td>
