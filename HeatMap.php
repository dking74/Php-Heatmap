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
    private $_border;

    private $_image = NULL;

    public function __construct($x_axis, $z_axis, $y_axis = array(), $width = 600, $height = 600, $config = array()) {
        $this->_width = $width;
        $this->_height = $height;

        global $heatmap_config_default;
        $this->_config = ConfigManager::merge($heatmap_config_default, $config);

        $this->_xaxis = $x_axis;
        $this->_yaxis = $y_axis;
        $this->_zaxis = $z_axis;

        // Make sure axes are correct, and end construction if not
        $this->_checkValidData();

        // After check of axes, we can create the image.
        // Go ahead and create the image and calibrate accordingly
        $this->_calibrate();
    }

    public function __destruct() {
        $this->_image->destroyImage();
    }

    /**
     * Public getter functions for individual private members
     */
    public function getConfig() { return $this->_config; }
    public function getImage()  { return $this->_image;  }
    public function getBorder() { return $this->_border; }
    public function getWidth()  { return $this->_width;  }
    public function getHeight() { return $this->_height; }

    /**
     * Public setter functions for changing private members
     */
    public function setData($data) {}
    public function setWidth($width) {}
    public function setHeight($height) {}

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

        if ($this->_width < 600 || $this->_height < 400) {
            throw new InsufficientSizeException(
                    "Either the width or height entered is too small to plot. Please have each one at or above 600px.");
        }
    }

    private function _createImage() {
        $this->_image = new Image($this->_width, $this->_height);

        // Make background color white
        $white = imagecolorallocate($this->_image->getImage(), 255, 255, 255);
        $this->_image->drawFilledRectangle(0, 0, $this->_width, $this->_height, $white);
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

        // Get the colorscale we are operating on; throw exception if no proper one found
        $colorscale = $this->_config["colorscale"];
        if (gettype($colorscale) !== "array" && gettype($colorscale[0]) !== "ColorMap")
            throw new InvalidColorscaleException("The colorscale you have inputted or changed must be of type \"ColorMap\".");

        // Define parameters for data contained inside heatmap
        $width  = $this->_width * 11 / 15;
        $height = $this->_height * 2 / 3;
        $starting_x = ($this->_width - $width) / 2;
        $starting_y = ($this->_height - $height) / 2;

        // Draw the data for heatmap, and then its border
        $this->_drawData($colorscale, $width, $height, $starting_x, $starting_y + $height);
        $this->_drawBorder($width, $height, $starting_x, $starting_y);

        $this->_addColorScale($colorscale, $starting_x + $width + 50, $starting_y + $height, $height);
        $this->_manageConfig();
    }

    private function _drawBorder($width, $height, $x0, $y0) {
        $black = $this->_image->createColor(0, 0, 0);
        $this->_image->drawOutlinedRectangle($x0, $y0, $width + $x0, $height + $y0, $black);

        // Save the border points so that we make adjustments within config
        // based on these locations that we saved
        $this->_border = array(
                array("x" => $x0, "y" => $y0),
                array("x" => $width + $x0, "y" => $height + $y0));
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

    private function _drawData($colorscale, $data_width, $data_height, $starting_x, $starting_y) {
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
        $this->_image->drawFilledRectangle($x0, $y0, $x1, $y1, $color);
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

    private function _addColorScale($colorscale, $x_data_edge, $y_data_edge, $data_height) {
        $num_colors = count($colorscale);
        $each_height_color = $data_height / $num_colors;

        $current_y = $y_data_edge;
        foreach ($colorscale as $color_num => $colormap) {
            $low_val  = $colormap->low;
            $high_val = $colormap->high;
            $red      = $colormap->color->red;
            $green    = $colormap->color->green;
            $blue     = $colormap->color->blue;

            $color = $this->_image->createColor($red, $green, $blue);
            $this->_image->drawFilledRectangle(
                          $x_data_edge, 
                          $current_y,
                          $x_data_edge + 30,
                          $current_y - $each_height_color,
                          $color);
            
            $current_y -= $each_height_color;
            $this->_image->drawText($high_val, $x_data_edge - 20, $current_y + 10, 0, 10);
        }
    }

    private function _manageConfig() {
        $manager = new HeatmapConfigManager($this);
        $manager->manage();
    }
}

} // End of HeatMap namespace

?>