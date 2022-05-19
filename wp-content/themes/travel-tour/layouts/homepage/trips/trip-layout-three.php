<section class="trips-list trips-list-one spacer">
<div class="container">
	<?php if( $title ) : ?><h4><?php echo esc_html( $title ); ?></h4><?php endif; ?>
	<div class="row">
		<?php $counter = 1; ?>
	<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<div class="col-sm-6  eq-blocks">
			<div class=" trips-list-block clearfix">
			<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' ); ?>
			<div class="trips-list-image"><img src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="img-responsive"></div>
			<div class="trips-list-caption">
				
				<h3><a href="<?php echo esc_url( get_the_permalink( $post->ID ) ); ?>"><?php the_title(); ?></a></h3>
				<div class="trips-list-info">


					<?php $trip_info = travel_tour_get_trip_info( $post->ID ); ?>
					<?php $trip_settings = travel_tour_trip_settings(); ?>

					<?php if( isset( $trip_info['trip_duration'] ) && $trip_info['trip_duration'] != '' ) : ?>
							<div class="trip_duration" aria-hidden="true">
								<i class="fa fa-clock-o"></i>
		                        <?php
		                        	esc_html_e( 'Duration: ','travel-tour' );
		                        	echo esc_attr( $trip_info['trip_duration'] );
		                        	if( $trip_info['trip_duration'] > 1 ) {
		                        		esc_html_e(' days','travel-tour');
		                        	} else {
		                        		esc_html_e(' day','travel-tour');
		                        	}

		                        	if( isset( $trip_info['trip_duration_nights'] ) && $trip_info['trip_duration_nights'] != '' ) {
		                        		echo " - " . esc_attr( $trip_info['trip_duration_nights'] );
		                        		if( $trip_info['trip_duration_nights'] > 1 ) {
			                        		esc_html_e(' nights','travel-tour');
			                        	} else {
			                        		esc_html_e(' night','travel-tour');
			                        	}
		                        	}
		                        ?>
		                    </div>
		            <?php endif; ?>


		            <?php
		                $code = 'USD';
		                if( isset( $trip_settings['currency_code'] ) && $trip_settings['currency_code'] != '' ) {
		                	$code = esc_attr( $trip_settings['currency_code'] );
		                }
		                $obj = new Wp_Travel_Engine_Functions();
		                $currency = $obj->wp_travel_engine_currencies_symbol( $code );
		                $cost = isset( $trip_info['trip_price'] ) ? $trip_info['trip_price']: '';
		                $prev_cost = isset( $trip_info['trip_prev_price'] ) ? $trip_info['trip_prev_price']: '';
		                if( $cost != '' ) { ?>
							<div class="trip_price">
			                    <?php
				                    esc_html_e( 'Price: ','travel-tour' );
				                    if( $prev_cost != '' && isset( $trip_info['sale'] ) ) {
                                        echo "<strike>";
                                        echo "<span>" . esc_attr( $currency . $obj->wp_travel_engine_price_format( $prev_cost ) . " " . $code ) . '</span>';
                                        echo '</strike>';
                                    }
				                    $obj = new Wp_Travel_Engine_Functions();
				                    echo ' ' . esc_attr( $currency . " " . $obj->wp_travel_engine_price_format( $cost ) . ' ' . $code );
			                    ?>
							</div>
		               	<?php }
		            ?>

				</div>
			</div>
		</div>
		</div>
	<?php $counter++; endwhile; wp_reset_postdata(); ?>
	</div>
</div>
</section>