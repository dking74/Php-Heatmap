<?php

namespace HeatMap;

class HeatMap {
    private $data;
    private $width;
    private $height;
    private $image = NULL;

    public function __construct(array $data = array(), $width = 500, $height = 500) {
        $this->data = $data;
        $this->width = $width;
        $this->height = $height;

        $this->setupImage();
        $this->calibrate();
    }

    public function getImage() { return $this->image(); }

    public function setData($data) {}
    public function setWidth($width) {}
    public function setHeight($height) {}

    public function addData($data) {}

    public function saveAsImage($filename, $type = 'png') {}
    public function writeToBrowser() {}

    private function isValidArray() {}

    private function setupImage() {
        $this->image = imagecreatetruecolor($this->width, $this->height)
                           or die("Unable to initialize new image.");
    }

    private function calibrate() {}
}

?>