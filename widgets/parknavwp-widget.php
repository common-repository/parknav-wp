<?php
/**
 * Elementor Parknav Widget.
 *
 * Elementor widget that inserts an embbedable Parknav content into the page.
 *
 * @since 1.0.0
 */


class Elementor_Parknav_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve Parknav widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'Parknav';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Parknav widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Parknav', 'plugin-name' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Parknav widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fa fa-code';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Parknav widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Register Parknav widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'plugin-name' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'name',
			[
				'label' => __( 'Name to embed', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Ai Incube HQ San Francisco', 'plugin-name' ),
			]
		);

		$this->add_control(
			'address',
			[
				'label' => __( 'lat,lon', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( '37.789921,-122.401787', 'plugin-name' ),
			]
		);

		$this->add_control(
			'zoom',
			[
				'label' => __( 'Zoom level', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 5,
				'max' => 20,
				'step' => 1,
				'default' => 15,
			]
		);

		$this->add_control(
			'height',
			[
				'label' => __( 'Height', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors' => [
					'{{WRAPPER}} .box' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render Parknav widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		
		$latlondefault = '37.789921,-122.401787';

		$settings = $this->get_settings_for_display();

		$name = $settings['name'];
		$zoom = $settings['zoom'];
		$address = $settings['address'];

		$latlonarr = explode(',', $address);
		if (count($latlonarr) < 2) {
			$address = $latlondefault;
			$latlonarr = explode(',', $address);
		}

		$latlon = 'lat=' . $latlonarr[0] . '&lon=' . $latlonarr[1]; 
		$height = $settings['height']['size'];
		$unit = $settings['height']['unit'];

		$pn_map_fixed_def = '&highColor=339933&mediumColor=cc9900&lowColor=ffffff&restrictedColor=b3b3b3&chanceThreshold=41&numGarages=4';



		echo '<div class="oembed-elementor-widget" style="height: ' . $height . $unit . '">';

		?><iframe id="parknav" width="100%" height="<?php echo $height . $unit;
		?>" frameborder="0" seamless="seamless" scrolling="no" marginheight="0" marginwidth="0" src="https://widget.parknav.com/simple?<?php echo $latlon;
		?>&zoom=<?php echo $zoom;
		echo $pn_map_fixed_def;
		?>" style="overflow: hidden; overflow-x: hidden; overflow-y: hidden; border: 0px;"></iframe><?php

		echo '</div>';

	}

}
