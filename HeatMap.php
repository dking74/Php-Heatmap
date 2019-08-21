<?php

namespace HeatMap;

require_once("heatmap_config.php");  // $heatmap_config_default
require_once("Image.php");

class HeatMap {
    private $_data;
    private $_config;

    private $_width;
    private $_height;
    private $_image = NULL;

    public function __construct(array $data = array(), $width = 500, $height = 500, $config = array()) {
        $this->_data = $data;
        $this->_config = $config;

        $this->_width = $width;
        $this->_height = $height;

        $this->_createImage();
        $this->_calibrate();
    }

    public function getImage() { return $this->_image(); }

    public function setData($data) {}
    public function setWidth($width) {}
    public function setHeight($height) {}

    public function addData($data) {}

    public function saveAsImage($filename, $type = 'png') {}
    public function writeToBrowser() {}

    private function isValidArray() {}

    private function _createImage() {
        $this->image = new Image($this->_width, $this->_height);
    }

    private function _calibrate() {}
}

?>