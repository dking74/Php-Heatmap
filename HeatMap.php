<?php

namespace HeatMap {

require_once("heatmap_config.php");  // $heatmap_config_default
require_once("Image.php");
require_once("Exceptions.php");

class HeatMap {
    private $_xaxis;
    private $_yaxis;
    private $_zaxis;

    private $_config;

    private $_width;
    private $_height;

    private $_image = NULL;

    public function __construct($x_axis, $y_axis, $z_axis, $width = 500, $height = 500, $config = array()) {
        $this->_width = $width;
        $this->_height = $height;

        $this->_config = $config;

        $this->_xaxis = $x_axis;
        $this->_yaxis = $y_axis;
        $this->_zaxis = $z_axis;

        // Make sure axes are correct, and end construction if so
        $this->_checkValidData();

        // After check of axes, we can create the image.
        // Go ahead and create the image and calibrate accordingly
        $this->_createImage();
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

    private function isValidArray() {}

    private function _checkValidData() {
        if (count($this->_xaxis) <= 0 || count($this->_yaxis) <= 0
             || count($this->_zaxis) <= 0 || count($this->_zaxis[0]) <= 0) {
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

    private function _calibrate() {
        // Define parameters for border of heatmap and draw it
        $width  = $this->_width * 11 / 15;
        $height = $this->_height * 2 / 3;
        $starting_x = ($this->_width - $width) / 2;
        $starting_y = ($this->_height - $height) / 2;
        $this->_drawBorder($width, $height, $starting_x, $starting_y);

        $num_x = count($this->_zaxis);
        $num_y = count($this->_zaxis[0]);
    }

    private function _drawBorder($width, $height, $x0, $y0) {
        $black = imagecolorallocate($this->_image->getImage(), 0, 0, 0);
        $this->_image->drawLine($x0, $y0, $width + $x0, $y0, $black);
        $this->_image->drawLine($width + $x0, $y0, $width + $x0, $height + $y0, $black);
        $this->_image->drawLine($width + $x0, $height + $y0, $x0, $height + $y0, $black);
        $this->_image->drawLine($x0, $height + $y0, $x0, $y0, $black);
    }

    private function _mapDataToBorder($data_value) {
        
    }
}

} // End of HeatMap namespace

?>