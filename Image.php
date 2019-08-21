<?php
namespace HeatMap;

require_once("image_config.php");

class Image {
    private $_width;
    private $_height;
    private $_image = NULL;

    public function __construct($width, $height) {
        $this->_width = $width;
        $this->_height = $height;

        $this->_setupImage();
    }

    public function getImage() { return $this->_image(); }

    public function setWidth($width) {}
    public function setHeight($height) {}

    public function setFont($font) {}

    public function reDraw() {}

    private function _setupImage() {
        $this->_image = imagecreatetruecolor($this->_width, $this->_height)
                           or die("Unable to initialize new image.");
    }
}

?>