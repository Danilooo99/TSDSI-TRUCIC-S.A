<?php

class Travel_Tour_Dropdown_Posttype_Taxonomies extends WP_Customize_Control {

	public $type = 'posttype-taxonomies';

	public $posttype = '';


	public function __construct( $manager, $id, $args = array() ) {

	    $travel_tour_posttype = 'trip';
	    if ( isset( $args['posttype'] ) ) {
	      $posttype_exist = post_type_exists( esc_attr( $args['posttype'] ) );
	      if ( true === $posttype_exist ) {
	        $our_taxonomy = esc_attr( $args['posttype'] );
	      }
	    }
	    $this->posttype = esc_attr( $travel_tour_posttype );

	    parent::__construct( $manager, $id, $args );
	 }

	protected function render_content() {
		
		$posttype = $this->posttype;
		$taxonomies = get_object_taxonomies( $posttype );
	?>

		<label>
	      <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
	         <select <?php echo $this->link(); ?>>
	            <?php
	              printf('<option value="%s" %s>%s</option>', '', selected($this->value(), '', false),__( 'Select', 'travel-tour') );
	             ?>
	            <?php if ( ! empty( $taxonomies ) ): ?>
	              <?php foreach ( $taxonomies as $key => $tax ): ?>
	                <?php $tax_name = str_replace( '_', ' ', $tax );
	                  printf('<option value="%s" %s>%s</option>', $tax, selected($this->value(), $tax, false), ucwords( $tax_name ) );
	                 ?>
	              <?php endforeach; ?>
	           <?php endif; ?>
	         </select>

	    </label>
	    <?php

	}
}