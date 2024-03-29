<?php

/**
 * Plugin Name: WP Google Charts Plus
 * Plugin URI: http://wordpress.org/extend/plugins/wp-google-charts/
 * Description: WP Google Charts Plus
 * Version: 1.0
 * Author: Caleb J. Evans
 * Author URI: http://www.scoottiecodes.com
 * Tags: google charts, charts, visualisation, bar chart, column chart, pie chart, chart chortcode, plugin
 * License: GPLv3

  =====================================================================================
  WP Google Charts Plus is based heavily on WP Google Charts. (http://gtk.org/foo) by Hmayak Tigranyan, copyright 2014.   WP Google Chart Plus is copyright Caleb J. Evans 2014.

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 3
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
  =====================================================================================
 */
class Wordpress_AdvancedGoogleCharts {

    public function init() {


        if (!is_admin()) {
            add_action('wp_head', array('Wordpress_AdvancedGoogleCharts', 'doAdvancedGoogleChartsHead'));
        }

        add_shortcode('agc', array('Wordpress_AdvancedGoogleCharts', 'doAdvancedGoogleChartsShortcode'));
    }

    public function doAdvancedGoogleChartsHead($atts) {

        if (is_singular()) {
            $post = get_queried_object();

            $pattern = get_shortcode_regex();
            preg_match_all("/$pattern/s", $post->post_content, $matches);
            if (isset($matches[2])) {
                $drawVisualizations = array();
                foreach ($matches[2] as $k => $value) {
                    if ($value == "agc") {
                        $params = "";
                        if (isset($matches[3][$k])) {
                            $params = $matches[3][$k];
                        }
                        $params = shortcode_parse_atts($params);
                        $defaults = array(
                            'title' => null,
                            'transpose' => '0',
                            'gid' => '0',
                            'pub' => '0',
                            'charttype' => 'columnchart',
                            'width'=>'600',
                            'height'=>'450',
                            'stacked'=>'false',
							'columns'=>''
                        );
                        $params = array_merge($defaults, $params);
                        if (!$params['key']) {
                            return;
                        }
                        
						
                        $spreadsheetUrl = "https://docs.google.com/spreadsheet/tq?key=";
                        $spreadsheetUrl.= $params['key'];
                        $spreadsheetUrl.= "&transpose=" . $params['transpose'];
                        $spreadsheetUrl.= "&headers=1";
                        $spreadsheetUrl.= "&gid=" . $params['gid'];
                        $spreadsheetUrl.= "&pub=" . $params['pub'];
                        
                        $chartID = $params['charttype'].$params['key'].$params['gid'].$params['transpose'].$params['stacked'].$params['width'].$params['height']. $post->post_type . $post->ID;
                        $chartID = preg_replace("/[^a-z0-9]/i", "", $chartID); // Remove special characters
						
						$chartID = strtolower($chartID);
						$drawVisualizations[] = "var query = new google.visualization.Query(
                         '".$spreadsheetUrl."');

                         query.send(handleQueryResponse".$chartID.");";
                               
                        ?>
                        
                        <script type="text/javascript">
                            
                              
                              
                            function handleQueryResponse<?php echo $chartID?>(response) {
                                                	 
                                if (response.isError()) {
                                    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
                                    return;
                                }
                                                		
                                var dataTable = response.getDataTable();
                                //var dataView = new google.visualization.DataView(dataTable);
                                var columncount = dataTable.getNumberOfColumns();
								var rowcount = dataTable.getNumberOfRows();
                                var dataView = new google.visualization.DataView(dataTable);
                               for(i = rowcount-1;i>-1;i--){
									var val = dataTable.getValue(i,0);
									if(val == null){
										dataTable.removeRow(i);
									}
                                }
								<?php
								
								if($params['columns']){
								$params['columns'] = explode(",",$params['columns']);
								$first = array_shift($params['columns']);
								?>
								var columns = [{"calc":"stringify","type":"string","sourceColumn":<?php echo $first?>},<?php echo implode(",",$params['columns']);?>];
								
								<?php
								}else{
								?>
								var columns = [{"calc":"stringify","type":"string","sourceColumn":0}];
								//var columns = [{calc: function(data, row) { return data.getFormattedValue(row, 0); }, type:'string',"type":"string","sourceColumn":0}];
                                for(i= 1;i<columncount;i++){
                                    columns[i] = i;
                                }
								<?php
								}
								?>
								dataView.setColumns(columns);

                        <?php
                        $chartdiv =  "agc" .$chartID ;                         ?>
						

								
								
								
				var options = {
					<?php if (is_string($params['title'])) { echo "title: '{$params['title']}',";} ?>
					isStacked:<?php echo $params['stacked']?>,
                                        width:<?php echo $params['width']?>,
                                        height:<?php echo $params['height']?>,
                                        chartArea:{left:40,top:30}
						};

                                var chart = new google.visualization.<?php echo ucfirst($params['charttype']).'Chart' ?>(document.getElementById('<?php echo $chartdiv ?>'));
                            chart.draw(dataView, options
                                );
								
								

                                                        
                                        }
                                        

                        </script>
                        <?php
                        
                    }
                }
                if($drawVisualizations){
                    ?>
                }
                     <script type="text/javascript" src="http://www.google.com/jsapi"></script>
                        <script type="text/javascript">
                            
                                    google.load('visualization', '1', {packages: ['corechart', 'columnchart','table']});
                           
                        </script>
                        <script type="text/javascript">
                            function drawVisualization() {
                             <?php  foreach( $drawVisualizations as $drawVisualization){
                                 echo $drawVisualization;
                             }
                                 ?>
                            }
                            google.setOnLoadCallback(drawVisualization);
                            </script>
                            <?php
                }
            }
        }
    }

    public function doAdvancedGoogleChartsShortcode($params, $content = null) {
        $defaults = array(
                            'transpose' => '0',
                            'gid' => '0',
                            'pub' => '0',
                            'charttype' => 'columnchart',
                            'width'=>'600',
                            'height'=>'450'
                        );
                        $params = array_merge($defaults, $params);
                      
        if ($params['key']) {
            $post = get_queried_object();
            $chartdiv =  "agc" .$params['charttype'].$params['key'].$params['gid'].$params['transpose'].$params['stacked'].$params['width'].$params['height']. $post->post_type . $post->ID;
                        
			$chartdiv = preg_replace("/[^a-z0-9]/i", "", $chartdiv); // Remove special characters
			
			$chartdiv = strtolower($chartdiv);
						
            return '<div id="'.$chartdiv.'" ></div>';
            
        }
    }

}

$advancedGoogleCharts = new Wordpress_AdvancedGoogleCharts();
$advancedGoogleCharts->init();
?>
