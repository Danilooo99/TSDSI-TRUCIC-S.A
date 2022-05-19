<?php
/**
 * Travel Agency Theme Info
 *
 * @package Travel_Agency
 */

function travel_agency_customizer_theme_info( $wp_customize ) {
	
    $wp_customize->add_section( 'theme_info', array(
		'title'       => __( 'Information Links' , 'travel-agency' ),
		'priority'    => 6,
	) );
    
    /** Important Links */
	$wp_customize->add_setting( 'theme_info_theme',
        array(
            'default' => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    
    $theme_info = '<div class="customizer-custom">';
    $theme_info .= '<h3 class="sticky_title">' . __( 'Need help?', 'travel-agency' ) . '</h3>';
    $theme_info .= '<span class="sticky_info_row"><label class="row-element">' . __( 'View demo', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://rarathemes.com/previews/?theme=travel-agency' ) . '" target="_blank">' . __( 'here', 'travel-agency' ) . '</a></span>';
    $theme_info .= '<span class="sticky_info_row"><label class="row-element">' . __( 'Recommended plugins', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://docs.rarathemes.com/docs/travel-agency/theme-installation-activation/recommended-plugins/' ) . '" target="_blank">' . __( 'here', 'travel-agency' ) . '</a></span>';
	$theme_info .= '<span class="sticky_info_row"><label class="row-element">' . __( 'View documentation', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://docs.rarathemes.com/docs/travel-agency/' ) . '" target="_blank">' . __( 'here', 'travel-agency' ) . '</a></span>';
    $theme_info .= '<span class="sticky_info_row"><label class="row-element">' . __( 'Support ticket', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://rarathemes.com/support-ticket/' ) . '" target="_blank">' . __( 'here', 'travel-agency' ) . '</a></span>';
	$theme_info .= '<span class="sticky_info_row"><label class="more-detail row-element">' . __( 'More Details', 'travel-agency' ) . ': </label><a href="' . esc_url( 'https://rarathemes.com/wordpress-themes/' ) . '" target="_blank">' . __( 'here', 'travel-agency' ) . '</a></span>';
	$theme_info .= '</div>';

	$wp_customize->add_control( new Travel_Agency_Info_Text( $wp_customize,
        'theme_info_theme', 
            array(
            	'label' => __( 'About Travel Agency' , 'travel-agency' ),
                'section'     => 'theme_info',
                'description' => $theme_info
            )
        )
    );
    
    /** Changing priority for static front page */
    $wp_customize->get_section( 'static_front_page' )->priority = 99;
    
    $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
    
}
add_action( 'customize_register', 'travel_agency_customizer_theme_info' );

if ( class_exists( 'WP_Customize_control' ) ) {

	class Travel_Agency_Info_Text extends Wp_Customize_Control {
		
		public function render_content(){ ?>
    	    <span class="customize-control-title">
    			<?php echo esc_html( $this->label ); ?>
    		</span>
    
    		<?php if( $this->description ){ ?>
    			<span class="description customize-control-description">
    			<?php echo wp_kses_post($this->description); ?>
    			</span>
    		<?php }
        }
	}
}

if( class_exists( 'WP_Customize_Section' ) ) :
/**
 * Adding Go to Pro Section in Customizer
 * https://github.com/justintadlock/trt-customizer-pro
 */
class Travel_Agency_Customize_Section_Pro extends WP_Customize_Section {

	/**
	 * The type of customize section being rendered.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'pro-section';

	/**
	 * Custom button text to output.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $pro_text = '';

	/**
	 * Custom pro button URL.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $pro_url = '';

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function json() {
		$json = parent::json();

		$json['pro_text'] = $this->pro_text;
		$json['pro_url']  = esc_url( $this->pro_url );

		return $json;
	}

	/**
	 * Outputs the Underscore.js template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	protected function render_template() { ?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} cannot-expand">
			<h3 class="accordion-section-title">
				{{ data.title }}
				<# if ( data.pro_text && data.pro_url ) { #>
					<a href="{{ data.pro_url }}" class="button button-secondary alignright" target="_blank">{{ data.pro_text }}</a>
				<# } #>
			</h3>
		</li>
	<?php }
}
endif;

add_action( 'customize_register', 'travel_agency_page_sections_pro' );
function travel_agency_page_sections_pro( $manager ) {
	// Register custom section types.
	$manager->register_section_type( 'Travel_Agency_Customize_Section_Pro' );

	// Register sections.
	$manager->add_section(
		new Travel_Agency_Customize_Section_Pro(
			$manager,
			'travel_agency_page_view_pro',
			array(
				'title'    => esc_html__( 'Pro Available', 'travel-agency' ),
                'priority' => 5, 
				'pro_text' => esc_html__( 'VIEW PRO THEME', 'travel-agency' ),
				'pro_url'  => 'https://rarathemes.com/wordpress-themes/travel-agency-pro/'
			)
		)
	);
}