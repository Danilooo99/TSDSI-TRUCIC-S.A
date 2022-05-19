( function( api ) {

	// Extends our custom "example-1" section.
	api.sectionConstructor['pro-section'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );

jQuery(document).ready(function($){
    //Scroll to section
    $('body').on('click', '#sub-accordion-panel-home_page_setting .control-subsection .accordion-section-title', function(event) {
        var section_id = $(this).parent('.control-subsection').attr('id');
        scrollToSection( section_id );
    });
    
    //preview url of homepages templates 
     wp.customize.panel( 'home_page_setting', function( section ){
        section.expanded.bind( function( isExpanded ) {
            if( isExpanded ){
                wp.customize.previewer.previewUrl.set( tadata.home );
            }
        });
    });
     
});

function scrollToSection( section_id ){
    var preview_section_id = "banner_section";

    var $contents = jQuery('#customize-preview iframe').contents();

    switch ( section_id ) {
        
         case 'accordion-section-about_us_section':
        preview_section_id = "about_section";
        break;

        case 'accordion-section-search_section':
        preview_section_id = "trip_search";
        break;

        case 'accordion-section-activities_section':
        preview_section_id = "activities_section";
        break;

        case 'accordion-section-popular_section':
        preview_section_id = "popular_section";
        break;

        case 'accordion-section-whyus_section':
        preview_section_id = "whyus_section";
        break;

        case 'accordion-section-featured_section':
        preview_section_id = "featured_section";
        break;
        
        case 'accordion-section-stat_section':
        preview_section_id = "stat_section";
        break;

        case 'accordion-section-deal_section':
        preview_section_id = "deal_section";
        break;
        
        case 'accordion-section-testimonial_section':
        preview_section_id = "testimonial_section";
        break;

        case 'accordion-section-cta_section':
        preview_section_id = "cta_section";
        break;

        case 'accordion-section-blog_section':
        preview_section_id = "blog_section";
        break;        
    }

    if( $contents.find('#'+preview_section_id).length > 0 && $contents.find('.home').length > 0 ){
        $contents.find("html, body").animate({
        scrollTop: $contents.find( "#" + preview_section_id ).offset().top
        }, 1000);
    }
}