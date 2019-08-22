<?php
namespace HeatMap {

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

    public function destroyImage() {
        imagedestroy($this->_image);
    }

    public function getImage() { return $this->_image; }

    public function setWidth($width) {}
    public function setHeight($height) {}

    public function setFont($font) {}

    public function redraw() {}

    public function createColor($R, $G, $B) { 
        return imagecolorallocate($this->_image, $R, $G, $B);
    }

    public function drawLine($x1, $y1, $x2, $y2, $color) {
        imageline($this->_image, $x1, $y1, $x2, $y2, $color);
    }

    public function drawRectangle($x1, $y1, $x2, $y2, $color) {
        imagefilledrectangle($this->_image, $x1, $y1, $x2, $y2, $color);
    }

    public function saveToPNG($filename) {
        imagepng($this->_image, $filename);
    }

    private function _setupImage() {
        $this->_image = imagecreatetruecolor($this->_width, $this->_height)
                           or die("Unable to initialize new image.");
    }
}

} // End of HeatMap namespace

?>