
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

			    var myData = [
			        { color:'#8ec657', data: [ [1,    <?php  echo $stuff['chart']['variables']['ssh_accept'];  ?>   ] ]},
			        { color:'#c65757', data: [ [1,    <?php  echo $stuff['chart']['variables']['ssh_failed'];  ?>   ] ]},
			        { color:'#ebc824', data: [ [1,    <?php  echo $stuff['chart']['variables']['ssh_invalid'];  ?>   ] ]}
			    ];

			    var myTotal = 	<?php  echo $stuff['chart']['variables']['ssh_accept'];  ?> +
			    				<?php  echo $stuff['chart']['variables']['ssh_failed'];  ?> +
			    				<?php  echo $stuff['chart']['variables']['ssh_invalid'];  ?>;

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
					stack: true,
					bars: {
						show: true,
						barWidth: 1,
						fill: 1,
	               		align: "center"
					},
					color: "#26ADE4"
				},

			        width: 0.5,
			        xaxis: {
			        	show: false, 
			        	min: 1
			        },
			        yaxis: {
			        	show: false, 
			        	max: myTotal, 
			        	min: 0, 
			        	reserveSpace: false, 
			        	labelWidth: 15
			        },
			        legend: { 
			        	show: false 
			        },
					tooltip: true,

					tooltipOpts: {

						content: function(label, xval, yval, flotItem) {
							return "Total " + yval;
				    	},

						shifts: {
							x: 20,
							y: -20
						},
						defaultTheme: false
					},

			     };
			
				$(function () {
                 
                	$("#minmax_<?php echo $chart->id; ?>").width(35).height(140);
                	$.plot($("#minmax_<?php echo $chart->id; ?>"),myData ,options);

				})

			})();
			</script>

            <td class="span1 hidden-phone" style="height: 170px">
                <div id="minmax_<?php echo $chart->id; ?>" style="width:35px;height:140px;top: 18px;right: 5px;"></div>
                <div style="position: relative; top: 13px;font-size: 11px;left: 3px;">All</div>
        	</td>
