<?php
class WTE_Show_Trip_Results
{
	function wte_show_result()
	{
		global $post;
		get_header(); ?>
		<div id="primary" class="content-area">
	    		<?php
	    		$options = get_option( 'wp_travel_engine_settings', array() );
				$pid = $options['pages']['search'];
				$nonce = wp_create_nonce('search-nonce');
				global $post;
				$wte_doc_tax_post_args = array(
	    			'post_type' => 'trip', // Your Post type Name that You Registered
	    			'posts_per_page' => -1,
	    			'order' => 'ASC',
				);
				$wte_doc_tax_post_qry = new WP_Query($wte_doc_tax_post_args);
				$max_cost = 0; $max_duration = 0;
		    	if($wte_doc_tax_post_qry->have_posts()) :
		       		while($wte_doc_tax_post_qry->have_posts()) :
	            		$wte_doc_tax_post_qry->the_post(); 
						$wp_travel_engine_setting = get_post_meta( $post->ID,'wp_travel_engine_setting',true );
        				$cost = isset( $wp_travel_engine_setting['trip_price'] ) ? $wp_travel_engine_setting['trip_price']: '';
						$prev_cost = isset($wp_travel_engine_setting['trip_prev_price']) ? $wp_travel_engine_setting['trip_prev_price']: '';
                        if( $cost!='' && isset($wp_travel_engine_setting['sale']) )
                        {
                        	$comp_cost = $prev_cost;
                        }
                        else{
                        	$comp_cost = $wp_travel_engine_setting['trip_prev_price'];
                        }

						if( $max_cost < $comp_cost )
						{
							$max_cost = $comp_cost;
						}
						if( $max_duration < $wp_travel_engine_setting['trip_duration'] )
						{
							$max_duration = $wp_travel_engine_setting['trip_duration'];
						}
					endwhile;
				endif;
				if( !isset( $_GET['search'] ) )
				{ ?>
					<form method="get" action='<?php echo esc_url(get_permalink($pid));?>' id="travel-tour-form-shortcode">
						<div class='advanced-search-wrapper'>
							<div class="sidebar">
								<h2><?php _e('FILTER BY','travel-tour'); ?></h2>
							<?php
							// if(isset($_GET['search']) && wp_verify_nonce( $_GET['search-nonce'], 'search-nonce' ) )
							// {			
					  			$msg = __('No results found!','travel-tour');

								if( !empty( $_GET['cat'] ) ) {
								    $cat = $_GET['cat'];
								}

								if( !empty( $_GET['budget'] ) ) {
								    $budget = $_GET['budget'];
								}

								if( !empty( $_GET['activities'] ) ) {
								    $activities = $_GET['activities'];
								}

								if( !empty( $_GET['destination'] ) ) {
								    $destination = $_GET['destination'];
								}

								if( !empty( $_GET['duration'] ) ) {
								    $duration = $_GET['duration'];
								}

								if( !empty( $_GET['cost'] ) ) {
								    $cost = $_GET['cost'];
								}
								
								if( !empty( $_GET['trip-date-select'] ) ) {
						    		$date = $_GET['trip-date-select'];
								}
								$response1 = ''; $response2 = ''; $response3 = '';
								$categories = get_categories('taxonomy=trip_types');
								if( is_array($categories) && sizeof($categories) > 0 )
								{
									$response1 = "<div class='advanced-search-field search-trip-type'><h3>".__('Trip Types','travel-tour')."</h3><ul>";
								}
								  
								foreach($categories as $category){
								    if($category->count > 0){
								    	
								        $response1.= "<li><label><input type='checkbox' name='cat' class='cat' value='".$category->slug."'><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
								    }
								}
								if( is_array($categories) && sizeof($categories) > 0 )
								{ 
									$response1.= "</ul></div>";
								}
								echo $response1;


								$categories = get_categories('taxonomy=activities');
					  			if( is_array($categories) && sizeof($categories) > 0 )
								{
									$response2 = "<div class='advanced-search-field search-activities'><h3>".__('Activities','travel-tour')."</h3><ul>";
								} 
								foreach($categories as $category){
								    if($category->count > 0){
								    	
								        $response2.= "<li><label><input type='checkbox' name='activities' class='input-activities' value='".$category->slug."'><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
								    }
								}
								if( is_array($categories) && sizeof($categories) > 0 )
								{ 
									$response2.= "</ul></div>";
								}
								echo $response2;

								

								$categories = get_categories('taxonomy=destination');
					  			if( is_array($categories) && sizeof($categories) > 0 )
								{
									$response3 = "<div class='advanced-search-field search-destination'><h3>".__('Destinations','travel-tour')."</h3>";
									$response3.= "<ul>";
								}
								  
								foreach($categories as $category){
								    if($category->count > 0){ 
								    	$response3.= "<li><label><input type='checkbox' name='destination' class='input-destination' value='".$category->slug."'><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
								    }
								}
								if( is_array($categories) && sizeof($categories) > 0 )
								{  
									$response3.= "</ul></div>";
								}
								  
								echo $response3;

								$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
            					
            					$default_posts_per_page = get_option( 'posts_per_page' );

								// Query arguments.
								$args = array(
								            'post_type'      				=> 'trip',
								            'posts_per_page' 				=> $default_posts_per_page,
								            'wpse_search_or_tax_query'      => true,
								            'paged' 						=> $paged
								        );

								$taxquery = array();
								$meta_query = array();
					  			
								if( !empty( $cat ) && $cat!= -1  ){
								    array_push($taxquery,array(
								            'taxonomy' => 'trip_types',
								            'field'    => 'slug',
								            'terms'    => $cat,
								            'include_children' => false,
								        ));
								}

								if( !empty($budget) && $budget!= -1 ){
								    array_push($taxquery,array(
								            'taxonomy' 	=> 'budget',
								            'field' 	=> 'slug',
								            'terms' 	=> $budget,
								            'include_children' => false,
								        ));
								}

								if(!empty($activities) && $activities!= -1 ) {
								    array_push($taxquery,array(
								            'taxonomy' 	=> 'activities',
								            'field' 	=> 'slug',
								            'terms' 	=> $activities,
								            'include_children' => false,
								        ));
								}

								if(!empty($destination) && $destination!= -1 ) {
								    array_push($taxquery,array(
								            'taxonomy' 	=> 'destination',
								            'field' 	=> 'slug',
								            'terms' 	=> $destination,
								            'include_children' => false,
								        ));
								}

								echo '<div class="advanced-search-field search-cost"><h3>'.__('Price','travel-tour').'</h3><div id="cost-slider-range"></div><div class="cost-slider-value"><span class="min-cost" id="min-cost" name="min-cost">0</span><span class="max-cost" id="max-cost" name="max-cost">'.$max_cost.'</span></div></div>';

								echo '<div class="advanced-search-field search-duration"><h3>'.__('Duration','travel-tour').'</h3><div id="duration-slider-range"></div><div class="duration-slider-value"><span id="min-duration" class="min-duration" name="min-duration">0</span><span class="max-duration" id="max-duration" name="max-duration">'.$max_duration.'</span></div></div>';
								do_action('wte_departure_date_dropdown');

								?>
							</div>
								<?php
								if(!empty($taxquery)){
			   					 	$args['tax_query'] = $taxquery;
								}

								$start_cost = 0; $end_cost = $max_cost; $start_dur = 0; $end_dur = $max_duration;
								if( isset( $_GET['min-cost'] ) && $_GET['min-cost']!='' )
								{
									$start_cost = (int) $_GET['min-cost'];
								}
								if( isset( $_GET['max-cost'] ) && $_GET['max-cost']!='' )
								{
									$end_cost 	= (int) $_GET['max-cost'];
								}
								if( isset( $_GET['min-duration'] ) && $_GET['min-duration']!='' )
								{
									$start_dur 	= (int) $_GET['min-duration'];
								}
								if( isset( $_GET['max-duration'] ) && $_GET['max-duration']!='' )
								{
									$end_dur 	= (int) $_GET['max-duration'];
								}

								array_push($meta_query,
								    array(
							            'key' 		=> 'wp_travel_engine_setting_trip_price',
							            'value' 	=> array($start_cost,$end_cost),
							            'compare' 	=> 'BETWEEN',
										'type'		=> 'NUMERIC'
							        )
								);
							    
								array_push($meta_query,
								    array(
							            'key' 		=> 'wp_travel_engine_setting_trip_duration',
							            'value' 	=> array($start_dur,$end_dur),
							            'compare' 	=> 'BETWEEN',
										'type'		=> 'NUMERIC'
							        )
								);

								if( !empty( $_GET['trip-date-select'] ) ) {
								    $date = $_GET['trip-date-select'];
									$arr = array();
									$arr['departure_dates']['sdate'] = $data;
									array_push($meta_query,
									    array(
								            'key' 		=> 'WTE_Fixed_Starting_Dates_setting',
								            'value' 	=> $arr['departure_dates']['sdate'],
								            'compare' 	=> 'LIKE',
								        )
									);
								}
							    $query = new WP_Query($args);
								?>
								<div id="loader" style="display: none">
							        <div class="table">
									    <div class="table-grid">
										    <div class="table-cell">
										    	<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
											</div>
										</div>
									</div>
								</div>
								<div class="travel-tour-wrap">
								   	<?php echo ($query->found_posts > 0) ? '<h3 class="foundPosts">' . $query->found_posts. __(' trip(s) found','travel-tour').'</h3>' : '<h3 class="foundPosts">'.apply_filters( 'no_result_found_message',$msg ).'</h3>'; ?>
									<div class="grid">
										<?php
								    	global $post;
										while ( $query->have_posts() ) {
											$query->the_post(); 
							    			$wp_travel_engine_setting = get_post_meta( $post->ID,'wp_travel_engine_setting',true );
                        					$wp_travel_engine_setting_option_setting = get_option( 'wp_travel_engine_settings', true );
								    			?>
											  	<div class="col">
	                                        		<div class="img-holder">
										                <a href="<?php the_permalink(); ?>" class="trip-post-thumbnail">
															<?php 
															$feat_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'trip-thumb-size' ); 
															if( isset($feat_image_url[0]) && $feat_image_url[0]!='' )
															{ ?>
						        								<img src="<?php echo esc_url( $feat_image_url[0] );?>">
				                
						        							<?php }  else{
				                                               echo '<img src="'.esc_url(  WP_TRAVEL_ENGINE_IMG_URL . '/public/css/images/trip-listing-fallback.jpg' ).'">';
				                                            }?>
														</a>
														   <?php
		                                                $code = 'USD';
		                                                if( isset($wp_travel_engine_setting_option_setting['currency_code']) && $wp_travel_engine_setting_option_setting['currency_code']!='')
		                                                {
		                                                    $code = esc_attr( $wp_travel_engine_setting_option_setting['currency_code'] );
		                                                }
		                                                $obj = new Wp_Travel_Engine_Functions();
		                                                $currency = $obj->wp_travel_engine_currencies_symbol( $code );
		                                                $cost = isset( $wp_travel_engine_setting['trip_price'] ) ? $wp_travel_engine_setting['trip_price']: '';
		                                                
		                                                $prev_cost = isset( $wp_travel_engine_setting['trip_prev_price'] ) ? $wp_travel_engine_setting['trip_prev_price']: '';

		                                                    $code = 'USD';
		                                                    if( isset( $wp_travel_engine_setting_option_setting['currency_code'] ) && $wp_travel_engine_setting_option_setting['currency_code']!= '' )
		                                                    {
		                                                        $code = $wp_travel_engine_setting_option_setting['currency_code'];
		                                                    } 
		                                                    $obj = new Wp_Travel_Engine_Functions();
		                                                    $currency = $obj->wp_travel_engine_currencies_symbol( $code );
		                                                    $prev_cost = isset($wp_travel_engine_setting['trip_prev_price']) ? $wp_travel_engine_setting['trip_prev_price']: '';
		                                                    if( $cost!='' && isset($wp_travel_engine_setting['sale']) )
		                                                    {
		                                                        $obj = new Wp_Travel_Engine_Functions();
		                                                        echo '<span class="price-holder"><span>'.esc_attr($currency).esc_attr( $obj->wp_travel_engine_price_format($cost) ).'</span></span>';
		                                                    }
		                                                    else{ 
		                                                        if( $prev_cost!='' )
		                                                        {
		                                                            $obj = new Wp_Travel_Engine_Functions();
		                                                            echo '<span class="price-holder"><span>'.esc_attr($currency).esc_attr( $obj->wp_travel_engine_price_format($prev_cost) ).'</span></span>';
		                                                        }
		                                                    }
		                                                    ?>
													</div>
													<div class="text-holder">
														<h3 class="entry-title"><a href="<?php echo esc_url( get_the_permalink() );?>"><?php the_title();?></a></h3>
														<?php
	                                                    if( isset( $wp_travel_engine_setting['trip_duration'] ) && $wp_travel_engine_setting['trip_duration']!='' )
	                                                    { ?>
	                                                        <div class="meta-info">
	                                                            <span class="time">
	                                                                <i class="fa fa-clock-o"></i>
	                                                                <?php echo esc_attr($wp_travel_engine_setting['trip_duration']); if($wp_travel_engine_setting['trip_duration']>1){ _e(' days','travel-tour');} else{ _e(' day','travel-tour'); }
	                                                                ?>
	                                                            </span>
	                                                        </div>
	                                                    <?php } ?>
	                                                    <div class="btn-holder">
	                                                        <a href="<?php echo esc_url( get_the_permalink() );?>" class="btn-more"><?php _e('View Detail','travel-tour');?></a>
	                                                    </div>
													</div>
										    	</div>
										<?php
										}
										?>
									</div>
								</div>
								   	<?php
								   	$obj = new Wte_Advanced_Search_Functions;
								   	$range = 4;
								   	$pages = $query->max_num_pages;
							        $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
							        $showitems = get_option( 'posts_per_page' );
							        if(!$pages)
							        {
							         $pages = 1;
							        }
							         
							        if(1 != $pages)
							        {
							            echo '<nav class="navigation pagination" style="clear:both;" role="navigation"><h2 class="screen-reader-text">Posts navigation</h2><div class="nav-links">';
							            if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>".__('First','travel-tour')."</a>";
							            if($paged > 1 && $showitems < $pages) echo "<a class='prev page-numbers' href='".get_pagenum_link($paged - 1)."'>".__('Previous','travel-tour')."</a>";

							            for ($i=1; $i <= $pages; $i++)
							            {
							            if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
							            {
							            echo ($paged == $i)? '<span aria-current="page" class="page-numbers current"><span class="meta-nav screen-reader-text"></span>'.$i."</span>":"<a class='page-numbers' href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
							            }
							            }

							            if ($paged < $pages && $showitems < $pages) echo "<a class='next page-numbers' href=\"".get_pagenum_link($paged + 1)."\">".__('Next','travel-tour')."</a>"; 
							            if ($paged < $pages-1 && $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>".__('Last','travel-tour')."</a>";
							            echo "</div></nav>\n";
							        }
						    		?> 
							<input type="hidden" name="search-nonce" id="search-nonce" value="<?php echo $nonce;?>">
						</div>
					</form>
					<?php
					echo 
				'<script>
				jQuery(document).ready(function($){
				var choices = {};
				$( "#duration-slider-range" ).slider({
					range: true,
					min: 0,
					max: '.$max_duration.',
					values: [ 0 , '.$max_duration.' ],
					slide: function( event, ui ) {
				        $("#min-duration").text(ui.values[0]);
					    $("#max-duration").text(ui.values[1]);
				    },
					stop: function( event, ui ) {
						$( "#duration-slider-range" ).slider( "disable" );
					    date = $( ".trip-date-select" ).val();
					    maxdur = ui.values[1];
					    mindur = ui.values[0];
					    $("#min-duration").text(ui.values[0]);
					    $("#max-duration").text(ui.values[1]);
					    mincost = $("#min-cost").text();
						maxcost = $("#max-cost").text();
					    nonce = $("#search-nonce").val();
						$(".advanced-search-field input[type=checkbox]").each(function() {
							if($(this).is(":checked"))
							{
				                if (!choices.hasOwnProperty(this.name)) {
				                	choices[this.name] = [];
				                }
				                choices[this.name].push(this.value);
				            }
				        });

				        jQuery.ajax({
					        type : "post",
					        url  : WTEAjaxData.ajaxurl,
					        data : {action: "wte_show_ajax_result", maxcost : maxcost, date : date, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
					        beforeSend: function(){
				            	$("#loader").fadeIn(500);
				        	},
				            success: function(response){
				            	$(".travel-tour-wrap").remove();
				            	$(".advanced-search-wrapper .pagination").remove();
				            	$(".advanced-search-wrapper").append(response);
								$( "#duration-slider-range" ).slider( "enable" );
				            },
				            complete: function(){
				            	$("#loader").fadeOut(500);             
				        	}
				    	});
					}
				});

				$( "#cost-slider-range" ).slider({
					range: true,
					min: 0,
					max: '.$max_cost.',
					values: [ 0, '.$max_cost.' ],
					slide: function( event, ui ) {
				        $("#min-cost").text(ui.values[0]);
					    $("#max-cost").text(ui.values[1]);
				    },
					stop: function( event, ui ) {
						$( "#cost-slider-range" ).slider( "disable" );
					    date = $( ".trip-date-select" ).val();
						maxcost = ui.values[1];
					    mincost = ui.values[0];
					    $("#min-cost").text(ui.values[0]);
					    $("#max-cost").text(ui.values[1]);
					    maxdur = $("#max-duration").text();
					    mindur = $("#min-duration").text();
					    nonce = $("#search-nonce").val();
						$(".advanced-search-field input[type=checkbox]").each(function() {
							if($(this).is(":checked"))
							{
				                if (!choices.hasOwnProperty(this.name)) {
				                	choices[this.name] = [];
				                }
				                choices[this.name].push(this.value);
				            }
				        });

				        jQuery.ajax({
					        type : "post",
					        url : WTEAjaxData.ajaxurl,
					        data : {action: "wte_show_ajax_result", maxcost : maxcost, date:date, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
					        beforeSend: function(){
				            	$("#loader").fadeIn(500);
				        	},
				            success: function(response){
				            	$(".travel-tour-wrap").remove();
				            	$(".advanced-search-wrapper .pagination").remove();
				            	$(".advanced-search-wrapper").append(response);
								$( "#cost-slider-range" ).slider( "enable" );
				            },
				            complete: function(){
				            	$("#loader").fadeOut(500);             
				        	}
				    	});
					}
				});

				$("body").on("change",".trip-date-select", function(){
				    nonce = $("#search-nonce").val();
				    date = $(this).val();
				    maxdur = $("#max-duration").text();
				    mindur = $("#min-duration").text();
				    mincost = $("#min-cost").text();
					maxcost = $("#max-cost").text();
					$(".advanced-search-field input[type=checkbox]").each(function() {
						if($(this).is(":checked"))
						{
			                if (!choices.hasOwnProperty(this.name)) {
			                	choices[this.name] = [];
			                }
			                choices[this.name].push(this.value);
			            }
			        });
			        jQuery.ajax({
				        type : "post",
				        url : WTEAjaxData.ajaxurl,
				        data : {action: "wte_show_ajax_result", date: date, maxcost : maxcost, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
				        beforeSend: function(){
			            	$("#loader").fadeIn(500);
			        	},
			            success: function(response){
			            	$(".travel-tour-wrap").remove();
			            	$(".advanced-search-wrapper .pagination").remove();
			            	$(".advanced-search-wrapper").append(response);
			            },
			            complete: function(){
			            	$("#loader").fadeOut(500);             
			        	}
			    	})
				});

				$("body").on("change",".advanced-search-field input[type=checkbox]", function(){
				    mincost = $("#min-cost").text();
					maxcost = $("#max-cost").text();
					mindur = $("#min-duration").text();
					maxdur = $("#max-duration").text();
					date = $( ".trip-date-select" ).val();

					$(".advanced-search-field input[type=checkbox]").each(function() {
						if($(this).is(":checked"))
						{
				            if (!choices.hasOwnProperty(this.name)) {
				            	choices[this.name] = [];
				            }
				            var idx = $.inArray(this.value, choices[this.name]);
							if (idx == -1) {
				            	choices[this.name].push(this.value);
				            }
				        }
				    });
				    value = this.value;
					if(!$(this).is(":checked"))
					{
						var idx = choices[this.name].indexOf(value);
				        if (idx > -1) {
							choices[this.name].splice(idx, 1);
						}
				    }
					nonce = $("#search-nonce").val();
				    jQuery.ajax({
				        type : "post",
				        url : WTEAjaxData.ajaxurl,
				        data : {action: "wte_show_ajax_result", date : date, maxcost : maxcost, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
				        beforeSend: function(){
				        	$("#loader").fadeIn(500);
				    	},
				        success: function(response){
				        	$(".travel-tour-wrap").remove();
				        	$(".advanced-search-wrapper .pagination, .advanced-search-wrapper .foundPosts").remove();
				        	$(".advanced-search-wrapper").append(response);
				        },
				        complete: function(){
				        	$("#loader").fadeOut(500);             
				    	}
				    });    
					});
					$("body").on("click",".load-more-search", function(e){
					e.preventDefault();
					var offset = $(".grid .col").length;
					mincost = $("#min-cost").text();
					maxcost = $("#max-cost").text();
					mindur = $("#min-duration").text();
					maxdur = $("#max-duration").text();
					date = $( ".trip-date-select" ).val();
					$(".advanced-search-field input[type=checkbox]").each(function() {
						if($(this).is(":checked"))
						{
				            if (!choices.hasOwnProperty(this.name)) {
				            	choices[this.name] = [];
				            }
				            var idx = $.inArray(this.value, choices[this.name]);
							if (idx == -1) {
				            	choices[this.name].push(this.value);
				            }
				        }
				    });
				    value = this.value;
				    if(choices.length > 0)
				    {
						if(!$(this).is(":checked"))
						{
							var idx = choices[this.name].indexOf(value);
					        if (idx > -1) {
								choices[this.name].splice(idx, 1);
							}
				        }
				    }
					nonce = $("#search-nonce").val();
					var t = $(this).parent();
					var dataid = $(this).parent().attr("data-id");
					var post_page = $(this).attr("data-id");
					post_page = parseInt(post_page);
				    jQuery.ajax({
				        type : "post",
				        url : WTEAjaxData.ajaxurl,
				        data : {action: "wte_show_ajax_result_load", maxcost : maxcost, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce, offset:offset, date:date, dataid:dataid},
				        beforeSend: function(){
				        	$("#loader").fadeIn(500);
				    	},
				        success: function(response){
				        	$(".travel-tour-wrap .grid").append(response);
				        },
				        complete: function(){
				        	dataid = parseInt(dataid);
				        	offset = parseInt(offset);
				        	offset = offset+post_page;
				        	if( dataid <= offset )
				        	{
								t.remove();
				        	}
				        	$("#loader").fadeOut(500);             
				    	}
				    }, this);    
				});
				});
				</script>';
				}
			else{ ?>
			<form method="get" action='<?php echo esc_url(get_permalink($pid));?>' id="travel-tour-form-shortcode">
				<div class='advanced-search-wrapper'>
					<div class="sidebar">
						<h2><?php _e('FILTER BY','travel-tour'); ?></h2>
					<?php
					// if(isset($_GET['search']) && wp_verify_nonce( $_GET['search-nonce'], 'search-nonce' ) )
					// {			
			  			$msg = __('No results found!','travel-tour');
						$cat = '';
						if( isset( $_GET['cat'] ) && !empty( $_GET['cat'] ) ) {
						    $cat = $_GET['cat'];
						}
						$budget = '';
						if( isset( $_GET['budget'] ) && !empty( $_GET['budget'] ) ) {
						    $budget = $_GET['budget'];
						}

						if( !empty( $_GET['activities'] ) ) {
						    $activities = $_GET['activities'];
						}

						if( !empty( $_GET['destination'] ) ) {
						    $destination = $_GET['destination'];
						}

						$response1 = ''; $response2 = ''; $response3 = '';

						$categories = get_categories('taxonomy=trip_types');
						if( is_array($categories) && sizeof($categories) > 0 )
						{
							$response1 = "<div class='advanced-search-field search-trip-type'><h3>".__('Trip Types','travel-tour')."</h3><ul>";
						}
						foreach($categories as $category){
						    if($category->count > 0){
						    	
						        if( $cat == $category->slug ){
								    	
						        	$response1.= "<li><label><input type='checkbox' name='cat' class='cat' value='".$category->slug."' checked><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
						    	}
						    	else{
						        	$response1.= "<li><label><input type='checkbox' name='cat' class='cat' value='".$category->slug."'><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
						    	}
						    }
						}
						if( is_array($categories) && sizeof($categories) > 0 )
						{  
							$response1.= "</ul></div>";
						}
						echo $response1;


						$categories = get_categories('taxonomy=activities');
			  			if( is_array($categories) && sizeof($categories) > 0 )
						{
							$response2 = "<div class='advanced-search-field search-activities'><h3>".__('Activities','travel-tour')."</h3><ul>";
						}
						foreach($categories as $category){
						    if($category->count > 0){
						    	
						        if( $activities == $category->slug ){
						        	$response2.= "<li><label><input type='checkbox' name='activities' class='activities' value='".$category->slug."' checked><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
						    	}
						    	else{
						        	$response2.= "<li><label><input type='checkbox' name='activities' class='activities' value='".$category->slug."'><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
						    	}
						    }
						}
						if( is_array($categories) && sizeof($categories) > 0 )
						{
							$response2.= "</ul></div>";
						}
						echo $response2;

						$categories = get_categories('taxonomy=destination');
			  			if( is_array($categories) && sizeof($categories) > 0 )
						{
							$response3 = "<div class='advanced-search-field search-destination'><h3>".__('Destinations','travel-tour')."</h3>";
							$response3.= "<ul>";
						}					  
						foreach($categories as $category){
						    if($category->count > 0){ 
						    	if( $destination == $category->slug ){
						        	$response3.= "<li><label><input type='checkbox' name='destination' class='destination' value='".$category->slug."' checked><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
						    	}
						    	else{
						        	$response3.= "<li><label><input type='checkbox' name='destination' class='destination' value='".$category->slug."'><span>".$category->name."</span></label><span class='count'>".$category->category_count."</span></li>";
						    	}
						    }
						}
						if( is_array($categories) && sizeof($categories) > 0 )
						{ 
							$response3.= "</ul></div>";
						}
						echo $response3;

						$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
            			$default_posts_per_page = get_option( 'posts_per_page' );

						// Query arguments.
						$args = array(
						            'post_type'      				=> 'trip',
						            'posts_per_page' 				=> $default_posts_per_page,
						            'wpse_search_or_tax_query'      => true,
						            'paged' 						=> $paged
						        );
						$min_dur = 0; $min_cost = 0;
						if( isset( $_GET['min-cost'] ) && $_GET['min-cost']!='' )
						{
							$min_cost = (int) $_GET['min-cost'];
						}
						if( isset( $_GET['max-cost'] ) && $_GET['max-cost']!='' )
						{
							$maximum_cost 	= (int) $_GET['max-cost'];
						}
						if( isset( $_GET['min-duration'] ) && $_GET['min-duration']!='' )
						{
							$min_dur 	= (int) $_GET['min-duration'];
						}
						if( isset( $_GET['max-duration'] ) && $_GET['max-duration']!='' )
						{
							$maximum_duration 	= (int) $_GET['max-duration'];
						}

						echo '<div class="advanced-search-field search-cost"><h3>'.__('Price','travel-tour').'</h3><div id="cost-slider-range"></div><div class="cost-slider-value"><span id="min-cost" class="min-cost" name="min-cost">'.$min_cost.'</span><span class="max-cost" id="max-cost" name="max-cost">'.$max_cost.'</span></div></div>';

						echo '<div class="advanced-search-field search-duration"><h3>'.__('Duration','travel-tour').'</h3><div id="duration-slider-range"></div><div class="duration-slider-value"><span class="min-duration" id="min-duration" name="min-duration">'.$min_dur.'</span><span id="max-duration" class="max-duration" name="max-duration">'.$max_duration.'</span></div></div>';
						do_action('wte_departure_date_dropdown',$_GET);
						?>
					</div>
						<?php
						$taxquery = array();
						$meta_query = array();

						if( !empty( $cat ) && $cat!= -1  ){
						    array_push($taxquery,array(
						            'taxonomy' => 'trip_types',
						            'field'    => 'slug',
						            'terms'    => $cat,
						            'include_children' => false,
						        ));
						}

						if( !empty($budget) && $budget!= -1 ){
						    array_push($taxquery,array(
						            'taxonomy' 	=> 'budget',
						            'field' 	=> 'slug',
						            'terms' 	=> $budget,
						            'include_children' => false,
						        ));
						}

						if(!empty($activities) && $activities!= -1 ) {
						    array_push($taxquery,array(
						            'taxonomy' 	=> 'activities',
						            'field' 	=> 'slug',
						            'terms' 	=> $activities,
						            'include_children' => false,
						        ));
						}

						if(!empty($destination) && $destination!= -1 ) {
						    array_push($taxquery,array(
						            'taxonomy' 	=> 'destination',
						            'field' 	=> 'slug',
						            'terms' 	=> $destination,
						            'include_children' => false,
						        ));
						}
						if(!empty($taxquery)){
	   					 	$args['tax_query'] = $taxquery;
						}
						$start_cost = 0; $end_cost = $max_cost; $start_dur = 0; $end_dur = $max_duration;
						if( isset( $_GET['min-cost'] ) && $_GET['min-cost']!='' )
						{
							$start_cost = (int) $_GET['min-cost'];
						}
						if( isset( $_GET['max-cost'] ) && $_GET['max-cost']!='' )
						{
							$end_cost 	= (int) $_GET['max-cost'];
						}
						if( isset( $_GET['min-duration'] ) && $_GET['min-duration']!='' )
						{
							$start_dur 	= (int) $_GET['min-duration'];
						}
						if( isset( $_GET['max-duration'] ) && $_GET['max-duration']!='.' )
						{
							$end_dur 	= (int) $_GET['max-duration'];
						}

						array_push($meta_query,
						    array(
					            'key' 		=> 'wp_travel_engine_setting_trip_price',
					            'value' 	=> array($start_cost,$end_cost),
					            'compare' 	=> 'BETWEEN',
								'type'		=> 'NUMERIC'
					        )
						);
					    
						array_push($meta_query,
						    array(
					            'key' 		=> 'wp_travel_engine_setting_trip_duration',
					            'value' 	=> array($start_dur,$end_dur),
					            'compare' 	=> 'BETWEEN',
								'type'		=> 'NUMERIC'
					        )
						);
						if( !empty( $_GET['trip-date-select'] ) ) {
						    $date = $_GET['trip-date-select'];
							$arr = array();
							$arr['departure_dates']['sdate'] = $date;
							array_push($meta_query,
							    array(
						            'key' 		=> 'WTE_Fixed_Starting_Dates_setting',
						            'value' 	=> $arr['departure_dates']['sdate'],
						            'compare' 	=> 'LIKE',
						        )
							);
						}

						if(!empty($meta_query)){
							 	$args['meta_query'] = $meta_query;
						}
						
					    $query = new WP_Query($args);
						?>
						<div id="loader" style="display: none">
					        <div class="table">
							    <div class="table-grid">
								    <div class="table-cell">
								    	<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="travel-tour-wrap">
						   	<?php echo ($query->found_posts > 0) ? '<h3 class="foundPosts">' . $query->found_posts. __(' trip(s) found','travel-tour').'</h3>' : '<h3 class="foundPosts">'.apply_filters( 'no_result_found_message',$msg ).'</h3>'; ?>
							<div class="grid">
								<?php
						    	global $post;
								while ( $query->have_posts() ) {
									$query->the_post(); 
					    			$wp_travel_engine_setting = get_post_meta( $post->ID,'wp_travel_engine_setting',true );
                					$wp_travel_engine_setting_option_setting = get_option( 'wp_travel_engine_settings', true );
						    			?>
									  	<div class="col">
                                    		<div class="img-holder">
								                <a href="<?php the_permalink(); ?>" class="trip-post-thumbnail">
													<?php 
													$feat_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'trip-thumb-size' ); 
													if( isset($feat_image_url[0]) && $feat_image_url[0]!='' )
													{ ?>
				        								<img src="<?php echo esc_url( $feat_image_url[0] );?>">
		                
				        							<?php }  else{
		                                               echo '<img src="'.esc_url(  WP_TRAVEL_ENGINE_IMG_URL . '/public/css/images/trip-listing-fallback.jpg' ).'">';
		                                            }?>
												</a>
												   <?php
                                                $code = 'USD';
                                                if( isset($wp_travel_engine_setting_option_setting['currency_code']) && $wp_travel_engine_setting_option_setting['currency_code']!='')
                                                {
                                                    $code = esc_attr( $wp_travel_engine_setting_option_setting['currency_code'] );
                                                }
                                                $obj = new Wp_Travel_Engine_Functions();
                                                $currency = $obj->wp_travel_engine_currencies_symbol( $code );
                                                $cost = isset( $wp_travel_engine_setting['trip_price'] ) ? $wp_travel_engine_setting['trip_price']: '';
                                                
                                                $prev_cost = isset( $wp_travel_engine_setting['trip_prev_price'] ) ? $wp_travel_engine_setting['trip_prev_price']: '';

                                                    $code = 'USD';
                                                    if( isset( $wp_travel_engine_setting_option_setting['currency_code'] ) && $wp_travel_engine_setting_option_setting['currency_code']!= '' )
                                                    {
                                                        $code = $wp_travel_engine_setting_option_setting['currency_code'];
                                                    } 
                                                    $obj = new Wp_Travel_Engine_Functions();
                                                    $currency = $obj->wp_travel_engine_currencies_symbol( $code );
                                                    $prev_cost = isset($wp_travel_engine_setting['trip_prev_price']) ? $wp_travel_engine_setting['trip_prev_price']: '';
                                                    if( $cost!='' && isset($wp_travel_engine_setting['sale']) )
                                                    {
                                                        $obj = new Wp_Travel_Engine_Functions();
                                                        echo '<span class="price-holder"><span>'.esc_attr($currency).esc_attr( $obj->wp_travel_engine_price_format($cost) ).'</span></span>';
                                                    }
                                                    else{ 
                                                        if( $prev_cost!='' )
                                                        {
                                                            $obj = new Wp_Travel_Engine_Functions();
                                                            echo '<span class="price-holder"><span>'.esc_attr($currency).esc_attr( $obj->wp_travel_engine_price_format($prev_cost) ).'</span></span>';
                                                        }
                                                    }
                                                    ?>
											</div>
											<div class="text-holder">
												<h3 class="entry-title"><a href="<?php echo esc_url( get_the_permalink() );?>"><?php the_title();?></a></h3>
												<?php
                                                if( isset( $wp_travel_engine_setting['trip_duration'] ) && $wp_travel_engine_setting['trip_duration']!='' )
                                                { ?>
                                                    <div class="meta-info">
                                                        <span class="time">
                                                            <i class="fa fa-clock-o"></i>
                                                            <?php echo esc_attr($wp_travel_engine_setting['trip_duration']); if($wp_travel_engine_setting['trip_duration']>1){ _e(' days','travel-tour');} else{ _e(' day','travel-tour'); }
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php } ?>
                                                <div class="btn-holder">
                                                    <a href="<?php echo esc_url( get_the_permalink() );?>" class="btn-more"><?php _e('View Detail','travel-tour');?></a>
                                                </div>
											</div>
								    	</div>
								<?php
								}
								?>
							</div>
						</div>
						   	<?php
						   	$obj = new Wte_Advanced_Search_Functions;
						   	$pages = $query->max_num_pages;
						   	$range = 4;
					        $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
					        $showitems = get_option( 'posts_per_page' );
					        if(!$pages)
					        {
					         $pages = 1;
					        }
					         
					        if(1 != $pages)
					        {
					            echo '<nav class="navigation pagination" style="clear:both;" role="navigation"><h2 class="screen-reader-text">Posts navigation</h2><div class="nav-links">';
					            if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>".__('First','travel-tour')."</a>";
					            if($paged > 1 && $showitems < $pages) echo "<a class='prev page-numbers' href='".get_pagenum_link($paged - 1)."'>".__('Previous','travel-tour')."</a>";

					            for ($i=1; $i <= $pages; $i++)
					            {
					            if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
					            {
					            echo ($paged == $i)? '<span aria-current="page" class="page-numbers current"><span class="meta-nav screen-reader-text"></span>'.$i."</span>":"<a class='page-numbers' href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
					            }
					            }

					            if ($paged < $pages && $showitems < $pages) echo "<a class='next page-numbers' href=\"".get_pagenum_link($paged + 1)."\">".__('Next','travel-tour')."</a>"; 
					            if ($paged < $pages-1 && $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>".__('Last','travel-tour')."</a>";
					            echo "</div></nav>\n";
					        }
				    		?>  
					<?php
					// } ?>
					<input type="hidden" name="search-nonce" id="search-nonce" value="<?php echo $nonce;?>">
				</div>
			</form>
			<?php
			global $post;
				$wte_doc_tax_post_args = array(
	    			'post_type' => 'trip', // Your Post type Name that You Registered
	    			'posts_per_page' => -1,
	    			'order' => 'ASC',
				);
				$wte_doc_tax_post_qry = new WP_Query($wte_doc_tax_post_args);
				$max_cost = 0; $max_duration = 0;
		    	if($wte_doc_tax_post_qry->have_posts()) :
		       		while($wte_doc_tax_post_qry->have_posts()) :
	            		$wte_doc_tax_post_qry->the_post(); 
						$wp_travel_engine_setting = get_post_meta( $post->ID,'wp_travel_engine_setting',true );
        				$cost = isset( $wp_travel_engine_setting['trip_price'] ) ? $wp_travel_engine_setting['trip_price']: '';
						$prev_cost = isset($wp_travel_engine_setting['trip_prev_price']) ? $wp_travel_engine_setting['trip_prev_price']: '';
                        if( $cost!='' && isset($wp_travel_engine_setting['sale']) )
                        {
                        	$comp_cost = $prev_cost;
                        }
                        else{
                        	$comp_cost = $wp_travel_engine_setting['trip_prev_price'];
                        }

						if( $max_cost < $comp_cost )
						{
							$max_cost = $comp_cost;
						}
						if( $max_duration < $wp_travel_engine_setting['trip_duration'] )
						{
							$max_duration = $wp_travel_engine_setting['trip_duration'];
						}
					endwhile;
				endif;
			echo 
				'<script type="text/javascript">
				jQuery(document).ready(function($){
				var choices = {};
				$( "#duration-slider-range" ).slider({
					range: true,
					min: 0,
					max: '.$max_duration.',
					values: [ 0 , '.$max_duration.' ],
					slide: function( event, ui ) {
				        $("#min-duration").text(ui.values[0]);
					    $("#max-duration").text(ui.values[1]);
				    },
					stop: function( event, ui ) {
						$( "#duration-slider-range" ).slider( "disable" );
						date = $( ".trip-date-select" ).val();
					    maxdur = ui.values[1];
					    mindur = ui.values[0];
					    $("#min-duration").text(ui.values[0]);
					    $("#max-duration").text(ui.values[1]);
					    mincost = $("#min-cost").text();
						maxcost = $("#max-cost").text();
					    nonce = $("#search-nonce").val();
						$(".advanced-search-field input[type=checkbox]").each(function() {
							if($(this).is(":checked"))
							{
				                if (!choices.hasOwnProperty(this.name)) {
				                	choices[this.name] = [];
				                }
				                choices[this.name].push(this.value);
				            }
				        });

				        jQuery.ajax({
					        type : "post",
					        url : WTEAjaxData.ajaxurl,
					        data : {action: "wte_show_ajax_result", date : date, maxcost : maxcost, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
					        beforeSend: function(){
				            	$("#loader").fadeIn(500);
				        	},
				            success: function(response){
				            	$(".travel-tour-wrap").remove();
				            	$(".advanced-search-wrapper .pagination").remove();
				            	$(".advanced-search-wrapper").append(response);
								$( "#duration-slider-range" ).slider( "enable" );
				            },
				            complete: function(){
				            	$("#loader").fadeOut(500);             
				        	}
				    	});
					}
				});

				$( "#cost-slider-range" ).slider({
					range: true,
					min: 0,
					max: '.$max_cost.',
					values: [ 0, '.$max_cost.' ],
					slide: function( event, ui ) {
				        $("#min-cost").text(ui.values[0]);
					    $("#max-cost").text(ui.values[1]);
				    },
					stop: function( event, ui ) {
						$( "#cost-slider-range" ).slider( "disable" );
						maxcost = ui.values[1];
					    mincost = ui.values[0];
					    date = $( ".trip-date-select" ).val();
					    $("#min-cost").text(ui.values[0]);
					    $("#max-cost").text(ui.values[1]);
					    maxdur = $("#max-duration").text();
					    mindur = $("#min-duration").text();
					    nonce = $("#search-nonce").val();
						$(".advanced-search-field input[type=checkbox]").each(function() {
							if($(this).is(":checked"))
							{
				                if (!choices.hasOwnProperty(this.name)) {
				                	choices[this.name] = [];
				                }
				                choices[this.name].push(this.value);
				            }
				        });

				        jQuery.ajax({
					        type : "post",
					        url : WTEAjaxData.ajaxurl,
					        data : {action: "wte_show_ajax_result", maxcost : maxcost, date : date, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
					        beforeSend: function(){
				            	$("#loader").fadeIn(500);
				        	},
				            success: function(response){
				            	$(".travel-tour-wrap").remove();
				            	$(".advanced-search-wrapper .pagination").remove();
				            	$(".advanced-search-wrapper").append(response);
								$( "#cost-slider-range" ).slider( "enable" );
				            },
				            complete: function(){
				            	$("#loader").fadeOut(500);             
				        	}
				    	});
					}
				});

				
				$("body").on("change",".trip-date-select", function(){
				    nonce = $("#search-nonce").val();
				    date = $(this).val();
				    maxdur = $("#max-duration").text();
				    mindur = $("#min-duration").text();
				    mincost = $("#min-cost").text();
					maxcost = $("#max-cost").text();
					$(".advanced-search-field input[type=checkbox]").each(function() {
						if($(this).is(":checked"))
						{
			                if (!choices.hasOwnProperty(this.name)) {
			                	choices[this.name] = [];
			                }
			                choices[this.name].push(this.value);
			            }
			        });
			        jQuery.ajax({
				        type : "post",
				        url : WTEAjaxData.ajaxurl,
				        data : {action: "wte_show_ajax_result", date: date, maxcost : maxcost, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
				        beforeSend: function(){
			            	$("#loader").fadeIn(500);
			        	},
			            success: function(response){
			            	$(".travel-tour-wrap").remove();
			            	$(".advanced-search-wrapper .pagination").remove();
			            	$(".advanced-search-wrapper").append(response);
			            },
			            complete: function(){
			            	$("#loader").fadeOut(500);             
			        	}
			    	})
				});



				$("body").on("change",".advanced-search-field input[type=checkbox]", function(){
				    mincost = $("#min-cost").text();
					maxcost = $("#max-cost").text();
					mindur = $("#min-duration").text();
					maxdur = $("#max-duration").text();
					date = $( ".trip-date-select" ).val();
					$(".advanced-search-field input[type=checkbox]").each(function() {
						if($(this).is(":checked"))
						{
				            if (!choices.hasOwnProperty(this.name)) {
				            	choices[this.name] = [];
				            }
				            var idx = $.inArray(this.value, choices[this.name]);
							if (idx == -1) {
				            	choices[this.name].push(this.value);
				            }
				        }
				    });
				    value = this.value;
					if(!$(this).is(":checked"))
					{
						var idx = choices[this.name].indexOf(value);
				        if (idx > -1) {
							choices[this.name].splice(idx, 1);
						}
				    }
					nonce = $("#search-nonce").val();
				    jQuery.ajax({
				        type : "post",
				        url : WTEAjaxData.ajaxurl,
				        data : {action: "wte_show_ajax_result", maxcost : maxcost, date : date, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce},
				        beforeSend: function(){
				        	$("#loader").fadeIn(500);
				    	},
				        success: function(response){
				        	$(".travel-tour-wrap").remove();
				        	$(".advanced-search-wrapper .pagination, .advanced-search-wrapper .foundPosts").remove();
				        	$(".advanced-search-wrapper").append(response);
				        },
				        complete: function(){
				        	$("#loader").fadeOut(500);             
				    	}
				    });    
					});
					$("body").on("click",".load-more-search", function(e){
					e.preventDefault();
					var offset = $(".grid .col").length;
					mincost = $("#min-cost").text();
					maxcost = $("#max-cost").text();
					mindur = $("#min-duration").text();
					maxdur = $("#max-duration").text();
					date = $( ".trip-date-select" ).val();
					$(".advanced-search-field input[type=checkbox]").each(function() {
						if($(this).is(":checked"))
						{
				            if (!choices.hasOwnProperty(this.name)) {
				            	choices[this.name] = [];
				            }
				            var idx = $.inArray(this.value, choices[this.name]);
							if (idx == -1) {
				            	choices[this.name].push(this.value);
				            }
				        }
				    });
				    value = this.value;
				    if(choices.length > 0)
				    {
						if(!$(this).is(":checked"))
						{
							var idx = choices[this.name].indexOf(value);
					        if (idx > -1) {
								choices[this.name].splice(idx, 1);
							}
				        }
				    }
					nonce = $("#search-nonce").val();
					var t = $(this).parent();
					var dataid = $(this).parent().attr("data-id");
					var post_page = $(this).attr("data-id");
					post_page = parseInt(post_page);
				    jQuery.ajax({
				        type : "post",
				        url : WTEAjaxData.ajaxurl,
				        data : {action: "wte_show_ajax_result_load", date : date, maxcost : maxcost, mincost : mincost, maxdur : maxdur, mindur : mindur, result : choices, nonce:nonce, offset:offset, dataid:dataid},
				        beforeSend: function(){
				        	$("#loader").fadeIn(500);
				    	},
				        success: function(response){
				        	$(".travel-tour-wrap .grid").append(response);
				        },
				        complete: function(){
				        	dataid = parseInt(dataid);
				        	offset = parseInt(offset);
				        	offset = offset+post_page;
				        	if( dataid <= offset )
				        	{
								t.remove();
				        	}
				        	$("#loader").fadeOut(500);             
				    	}
				    }, this);    
				});
				});
				</script>';
		}
		?>
	</div>
	<?php get_footer(); 
	}
}

new WTE_Show_Trip_Results;