<?php
namespace HeatMap {

require_once("../Colors.php");

$heatmap_config_default = array(
    // Minimum and maximum values for data values
    "min_value" => 0,
    "max_value" => 100,

    // Width and height of heatmap
    "width" => 700,
    "height" => 500,

    // Color choice for values on map
    "colorscale" => ColorScale::$RGB,

    // Axis and title information
    "title"         => "",
    "titleFontSize" => 13,
    "titleAngle"    => 0,

    // Booleans for specific, extra elements on heatmap
    "useSeparators" => true,
    "useColorMap"   => true,
    "useBorder"     => true,
);

} // End of HeatMap namespace

?>