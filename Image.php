<?php
namespace HeatMap {

require_once("image_config.php");

class Image {
    private $_width;
    private $_height;

    private $_font = NULL;

    private $_image = NULL;

    public function __construct($width, $height) {
        $this->_width = $width;
        $this->_height = $height;

        $this->_font = __DIR__."/fonts/arial.ttf";

        $this->_image = imagecreatetruecolor($this->_width, $this->_height)
                           or die("Unable to initialize new image.");
    }

    public function destroyImage() {
        if ($this->_image !== NULL) imagedestroy($this->_image);
    }

    public function getFont()  { return $this->_font;  }
    public function getImage() { return $this->_image; }

    /**
     * Public setters to manipulate private member variables
     */
    public function setWidth($width) {}
    public function setHeight($height) {}
    public function setFont($font_file) { $this->_font = $font_file; }

    public function redraw() {}

    private function _checkValidColor($R, $G, $B) {
        if ($R > 255 || $R < 0 || $G > 255 || $G < 0 || $B > 255 || $B < 0) {
            return false;
        }

        return true;
    }

    public function createColor($R, $G, $B) {
        $valid_color = $this->_checkValidColor($R, $G, $B);
        if ($valid_color) return imagecolorallocate($this->_image, $R, $G, $B);

        return imagecolorallocate($this->_image, 0, 0, 0);
    }

    public function drawText($message, $x, $y, $angle = 0, $size = 8, $color = array()) {
        $color_d = NULL;
        if (!$color || count($color) !== 3) {
            $color_d = $this->createColor(0, 0, 0);
        } else {
            $color_d = $this->createColor($color[0], $color[1], $color[2]);
        }

        return imagettftext($this->_image, $size, $angle, $x, $y, $color_d, $this->_font, $message);
    }

    public function drawLine($x1, $y1, $x2, $y2, $color) {
        return imageline($this->_image, $x1, $y1, $x2, $y2, $color);
    }

    public function drawOutlinedRectangle($x1, $y1, $x2, $y2, $color) {
        return imagerectangle($this->_image, $x1, $y1, $x2, $y2, $color);
    }

    public function drawFilledRectangle($x1, $y1, $x2, $y2, $color) {
        return imagefilledrectangle($this->_image, $x1, $y1, $x2, $y2, $color);
    }

    public function saveToPNG($filename) {
        return imagepng($this->_image, $filename);
    }
}

} // End of HeatMap namespace

?>