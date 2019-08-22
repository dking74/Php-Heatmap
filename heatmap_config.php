<?php
namespace HeatMap {

require_once("Colors.php");

$heatmap_config_default = array(
    // Minimum and maximum values for data values
    "min_value" => 0,
    "max_value" => 100,

    // Color choice for values on map
    "colorscale" => ColorScale::$RGB
);

} // End of HeatMap namespace

?>