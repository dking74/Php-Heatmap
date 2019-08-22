<?php

namespace HeatMap {

require_once("heatmap_config.php");
require_once("Image.php");
require_once("Exceptions.php");
require_once("ConfigManager.php");

class HeatMap {
    private $_xaxis;
    private $_yaxis;
    private $_zaxis;

    private $_config;

    private $_width;
    private $_height;

    private $_image = NULL;

    public function __construct($x_axis, $z_axis, $y_axis = array(), $width = 500, $height = 500, $config = array()) {
        $this->_width = $width;
        $this->_height = $height;

        global $heatmap_config_default;
        $this->_config = ConfigManager::merge($heatmap_config_default, $config);

        $this->_xaxis = $x_axis;
        $this->_yaxis = $y_axis;
        $this->_zaxis = $z_axis;

        // Make sure axes are correct, and end construction if so
        $this->_checkValidData();

        // After check of axes, we can create the image.
        // Go ahead and create the image and calibrate accordingly
        $this->_calibrate();
    }

    public function __destruct() {
        $this->_image->destroyImage();
    }

    public function getImage() { return $this->_image; }

    public function setData($data) {}

    public function setWidth($width) {}
    public function setHeight($height) {}

    public function addData($data) {}

    public function saveAsImage($filename, $type = 'png') {
        $this->_image->saveToPNG($filename);
    }

    public function writeToBrowser() {}

    private function _checkValidData() {
        if (count($this->_xaxis) <= 0 || count($this->_zaxis) <= 0 || count($this->_zaxis[0]) <= 0) {
            throw new EmptyAxisException("One of your axis does not have data. Please make sure you enter valid axes for HeatMap construction.");
        }

        if (count($this->_zaxis) !== count($this->_xaxis)) {
            throw new InvalidDimensionsException(
                    "The z-axis dimensions do not match that of your x-axis dimension and/or y-axis dimensions. " .
                    "Please ensure the z-axis is a 2-dimensional array with the number of sub-arrays matching the number of ".
                    "items in the x-axis.");
        }
    }

    private function _createImage() {
        $this->_image = new Image($this->_width, $this->_height);

        // Make background color white
        $white = imagecolorallocate($this->_image->getImage(), 255, 255, 255);
        $this->_image->drawRectangle(0, 0, $this->_width, $this->_height, $white);
    }

    private function _destroyImage() {
        if ($this->_image !== NULL) {
            $this->_image->destroyImage();
            $this->_image = NULL;
        }
    }

    private function _calibrate() {
        $this->_destroyImage();
        $this->_createImage();

        // Define parameters for data contained inside heatmap
        $width  = $this->_width * 11 / 15;
        $height = $this->_height * 2 / 3;
        $starting_x = ($this->_width - $width) / 2;
        $starting_y = ($this->_height - $height) / 2;

        // Draw the data for heatmap, and then its border
        $this->_drawData($width, $height, $starting_x, $starting_y + $height);
        $this->_drawBorder($width, $height, $starting_x, $starting_y);
    }

    private function _drawBorder($width, $height, $x0, $y0) {
        $black = $this->_image->createColor(0, 0, 0);
        $this->_image->drawLine($x0, $y0, $width + $x0, $y0, $black);
        $this->_image->drawLine($width + $x0, $y0, $width + $x0, $height + $y0, $black);
        $this->_image->drawLine($width + $x0, $height + $y0, $x0, $height + $y0, $black);
        $this->_image->drawLine($x0, $height + $y0, $x0, $y0, $black);
    }

    private function _drawDataSeparators($num_x, $starting_x, $x_width, $starting_y, $data_height) {
        // Draw a line to separate each column
        $white_color = $this->_image->createColor(0, 0, 0);
        $x_coord = $starting_x + $x_width;
        for ($i = 1; $i < $num_x; $i++) {
            $this->_image->drawLine($x_coord, $starting_y, $x_coord, $starting_y - $data_height, $white_color);
            $x_coord += $x_width;
        }
    }

    private function _drawData($data_width, $data_height, $starting_x, $starting_y) {
        $colorscale = $this->_config["colorscale"];
        if (gettype($colorscale) !== "ColorMap")
            throw new InvalidColorscaleException("The colorscale you have inputted or changed must be of type \"ColorMap\".");

        // Define the number of x quadrants we have and the width of each one
        $num_x = count($this->_zaxis);
        $x_width = $data_width / $num_x;

        // Define the current points for drawing; defaults to starting index
        $current_x = $starting_x;
        $current_y = $starting_y;

        // Iterate through each data point and draw to image
        foreach($this->_zaxis as $x_num => $x_data) {
            // Get the number of elements in the x array.
            // Use that information to calculate each y array height.
            $num_y = count($x_data);
            if ($num_y === 0) $num_y = 1;
            $y_height = $data_height / $num_y;

            foreach($x_data as $data_num => $data_point) {
                $this->_mapDataToBorder(
                          $current_x, 
                          $current_y,
                          $current_x + $x_width,
                          $current_y - $y_height,
                          $data_point,
                          $colorscale);
                $current_y -= $y_height;
            }

            // Increase the x-value by an x width away.
            // Make the current y back to the start value
            $current_x += $x_width;
            $current_y = $starting_y; 
        }

        $this->_drawDataSeparators($num_x, $starting_x, $x_width, $starting_y, $data_height);        
    }

    private function _mapDataToBorder($x0, $y0, $x1, $y1, $data_value, $colorscale) {
        $color = $this->_getDataColor($colorscale, $data_value);
        $this->_image->drawRectangle($x0, $y0, $x1, $y1, $color);
    }

    private function _getDataColorMap($colorscale, $data_value) {
        foreach ($colorscale as $index => $colormap) {
            if ($data_value >= $colormap->low && $data_value <= $colormap->high) {
                return $colormap;
            }
        }

        return NULL;
    }

    private function _getDataColor($colorscale, $data_value) {
        $colormap = $this->_getDataColorMap($colorscale, $data_value);
        if ($colormap != NULL) {
            $red   = $colormap->color->red;
            $green = $colormap->color->green;
            $blue  = $colormap->color->blue;

            return $this->_image->createColor($red, $green, $blue);
        }

        return $this->_image->createColor(255, 255, 255);
    }

    private function _manageConfig()
}

} // End of HeatMap namespace

?>