<?php

namespace HeatMap {

require_once("Image.php");
require_once("Exceptions.php");
require_once("./config/Heatmap.config.php");
require_once("./config/ConfigManager.php");

class HeatMap {
    /** @var Array $_xaxis */ private $_xaxis;
    /** @var Array $_yaxis */ private $_yaxis;
    /** @var Array $_zaxis */ private $_zaxis;

    /** @var Array $_config */ private $_config;

    /** @var Int $_width  */ private $_width;
    /** @var Int $_height */ private $_height;

    /** @var \HeatMap\Image $_image */
    private $_image = NULL;

    /** @var \HeatMap\HeatmapConfigManager $_configManager */ 
    private $_configManager = NULL;

    public function __construct(
        $x_axis,
        $z_axis,
        $y_axis = array(),
        $config = array()
    ) {
        $this->mergeGlobalConfig($config);

        $this->_width = $this->_config['width'] or 700;
        $this->_height = $this->_config['height'] or 500;

        $this->_xaxis = $x_axis;
        $this->_yaxis = $y_axis;
        $this->_zaxis = $z_axis;

        // Make sure axes are correct, and end construction if not
        $this->checkValidData();

        // After check of axes, we can create the image.
        // Go ahead and create the image and calibrate accordingly.
        $this->calibrate();
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
    public function setXaxis($x_axis)  { $this->_zaxis  = $x_axis; $this->checkValidData(); $this->calibrate(); }
    public function setYaxis($y_axis)  { $this->_yaxis  = $y_axis; $this->checkValidData(); $this->calibrate(); }
    public function setZaxis($z_axis)  { $this->_zaxis  = $z_axis; $this->checkValidData(); $this->calibrate(); }
    public function setWidth($width) {
        $this->_width  = $width;
        $this->checkValidData();

        // The individual with has already been adjusted, but keep
        // up-to-date with the current config as well.
        $this->_config['width'] = $width;
        $this->calibrate();
    }
    public function setHeight($height) {
        $this->_height = $height;
        $this->checkValidData();

        // The individual height has already been adjusted, but keep
        // up-to-date with the current config as well.
        $this->_config['height'] = $height;
        $this->calibrate();
    }
    public function setConfig($config) {
        $this->mergeGlobalConfig($config);
        $this->calibrate();
    }

    /**
     * Save the current heatmap generated to downloadable, file output.
     * 
     * @param String $filename The name of the file to generate
     * @param String $type The extension to generate the file as (default: "png")
     */
    public function saveAsImage($filename, $type = 'png') {
        $this->_image->save($filename, $type);
    }

    /**
     * Merge the global config with locally entered config. Set
     * the internal config instance with merged value.
     * @internal
     * 
     * @param Array $config The config to merge with global instance.
     */
    private function mergeGlobalConfig($config) {
        global $heatmap_config_default;
        $this->_config = ConfigManager::merge($heatmap_config_default, $config);
    }

    /**
     * Check and make sure that the axis', as entered by user, are correct
     * and within the constraints of the application.
     * @internal
     * 
     * @throws EmptyAxisException If the x-axis or z-axis data has not been set.
     * @throws InvalidDimensionsException If the number of entries in x-axis does not match the number in z-axis.
     * @throws InsufficientSizeException If the dimensions of width + height are too small.
     */
    private function checkValidData() {
        if (count($this->_xaxis) <= 0 || count($this->_zaxis) <= 0 || count($this->_zaxis[0]) <= 0) {
            throw new EmptyAxisException(
                "One of your axis does not have data. Please make sure you enter valid axes for HeatMap construction.");
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

    /**
     * Responsible for creating the current image operating on.
     * @internal 
     */
    private function createImage() {
        $this->_image = new Image($this->_width, $this->_height);

        // Make background color white
        $white = imagecolorallocate($this->_image->getImage(), 255, 255, 255);
        $this->_image->drawFilledRectangle(0, 0, $this->_width, $this->_height, $white);
    }

    /**
     * Responsible for cleaning up memory associated with the current image.
     * @internal
     */
    private function destroyImage() {
        if ($this->_image !== NULL) {
            $this->_image->destroyImage();
            $this->_image = NULL;
        }
    }

    /**
     * Main operating, internal function for drawing the heatmap.
     * This method is called any time that the config is changed and the image
     * needs to be recalibrated for current needs.
     * 
     * @internal
     * 
     * @throws InvalidColorscaleException If the config colorscale is not supported.
     */
    private function calibrate() {
        // Destroy and then create the image. We always
        // want to tie up loose images created before re-calibrating.
        $this->destroyImage();
        $this->createImage();

        // Get the colorscale we are operating on; throw exception if no proper one found
        $colorscale = $this->_config["colorscale"];
        if (gettype($colorscale) !== "array" && gettype($colorscale[0]) !== "ColorMap")
            throw new InvalidColorscaleException("The colorscale you have inputted or changed must be of type \"ColorMap\".");

        // Define parameters for data contained inside heatmap
        $width  = $this->_width * 4 / 5;
        $height = $this->_height * 4 / 5;
        $starting_x = ($this->_width - $width) / 2;
        $starting_y = ($this->_height - $height) / 2;

        // Draw the data for heatmap, and then its border
        $this->drawData($colorscale, $width, $height, $starting_x, $starting_y + $height);
        $this->drawBorder($width, $height, $starting_x, $starting_y);

        $include_colormap = $this->_config['useColorMap'] or true;
        if ($include_colormap) {
            $this->addColorScale($colorscale, $starting_x + $width + 50, $starting_y + $height, $height);
        }
        $this->manageConfig();
    }

    /**
     * Draws the border of the heatmap.
     * @internal
     * 
     * @param Int $width The width of the image
     * @param Int $height The height of the image
     * @param Int $x0 The starting x point
     * @param Int $y0 The starting y point
     */
    private function drawBorder($width, $height, $x0, $y0) {
        $black = $this->_image->createColor(0, 0, 0);
        $this->_image->drawOutlinedRectangle($x0, $y0, $width + $x0, $height + $y0, $black);

        // Save the border points so that we make adjustments within config
        // based on these locations that we saved
        $this->_border = array(
            array("x" => $x0, "y" => $y0),
            array("x" => $width + $x0, "y" => $height + $y0));
    }

    /**
     * Draw a line in between each x-axis entry.
     * @internal
     * 
     * @param Int $num_x The number of x values to generate.
     * @param Int $x0 The x-value for what to start vertical line separator.
     * @param Int $x_width The width of the line separator.
     * @param Int $y0 The y-value where to start the line separator generation.
     * @param Int $data_height The height of where to end the line separator.
     */
    private function drawDataSeparators($num_x, $x0, $x_width, $y0, $data_height) {
        // Draw a line to separate each column
        $white_color = $this->_image->createColor(0, 0, 0);
        $x_coord = $x0 + $x_width;
        for ($i = 1; $i < $num_x; $i++) {
            $this->_image->drawLine($x_coord, $y0, $x_coord, $y0 - $data_height, $white_color);
            $x_coord += $x_width;
        }
    }

    /**
     * Draw every data points to the current heatmap.
     * @internal
     * 
     * @param \HeatMap\ColorScale $colorscale The scale to use for color mapping.
     * @param Int $data_width The width of the data area
     * @param Int $data_height The height of the data area
     * @param Int $starting_x The starting x-point for data to be generated
     * @param Int $starting_y The starting y-point for data to be generated
     */
    private function drawData($colorscale, $data_width, $data_height, $starting_x, $starting_y) {
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
                $this->mapDataToBorder(
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

        $this->drawDataSeparators($num_x, $starting_x, $x_width, $starting_y, $data_height);
    }

    /**
     * Map The current data to be drawn from border-to-border.
     * @internal
     * 
     * @param Int $x0 The start x-value
     * @param Int $y0 The start y-value
     * @param Int $x1 The end x-value
     * @param Int $y1 The end y-value
     * @param Int $data_value The data point to map
     * @param \HeatMap\ColorScale $colorscale The scale to use for color sequence.
     */
    private function mapDataToBorder($x0, $y0, $x1, $y1, $data_value, $colorscale) {
        $color = $this->getDataColor($colorscale, $data_value);
        $this->_image->drawFilledRectangle($x0, $y0, $x1, $y1, $color);
    }

    /**
     * Get the specific color (R,G,B values) corresponding to data point.
     * @internal
     * 
     * @param \HeatMap\ColorScale $colorscale The colorscale being utilized
     * @param Int $data_value The data point attempting to find R/G/B for.
     * 
     * @return \HeatMap\Color The color found
     */
    private function getDataColorMap($colorscale, $data_value) {
        foreach ($colorscale as $index => $colormap) {
            if ($data_value >= $colormap->low && $data_value <= $colormap->high) {
                return $colormap;
            }
        }

        return NULL;
    }

    /**
     * Get the proper color corresponding to the current data value.
     * @internal
     * 
     * @param \HeatMap\ColorScale $colorscale The colorscale being utilized
     * @param Int $data_value The data point attempting to find color for.
     * 
     * @return Int The color value allocated 
     */
    private function getDataColor($colorscale, $data_value) {
        $colormap = $this->getDataColorMap($colorscale, $data_value);
        if ($colormap != NULL) {
            $red   = $colormap->color->red;
            $green = $colormap->color->green;
            $blue  = $colormap->color->blue;

            return $this->_image->createColor($red, $green, $blue);
        }

        return $this->_image->createColor(255, 255, 255);
    }

    /**
     * Add the colorscale to the current heatmap menu to be
     * able to visualize what each color on map represents.
     * 
     * @internal
     * 
     * @param Array $colorscale The scale to use for color documenting.
     * @param Int $x_data_edge The x-point for the data ends
     * @param Int $y_data_edge The y-point for the data ends
     * @param Int $data_height The height of the data
     */
    private function addColorScale($colorscale, $x_data_edge, $y_data_edge, $data_height) {
        $num_colors = count($colorscale);
        $each_height_color = $data_height / $num_colors;

        $current_y = $y_data_edge;
        foreach ($colorscale as $color_num => $colormap) {
            // $low_val  = $colormap->low;
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
                $color
            );
            
            $current_y -= $each_height_color;
            $this->_image->drawText($high_val, $x_data_edge - 20, $current_y + 10, 0, 10);
        }
    }

    /**
     * Create configManager (if not already created) and allow it
     * to manage what the config is currently set to for merging.
     * 
     * @internal
     */
    private function manageConfig() {
        if ($this->_configManager == NULL) {
            $this->_configManager = new HeatmapConfigManager($this);
        }
        $this->_configManager->manage();
    }
}

} // End of HeatMap namespace

?>