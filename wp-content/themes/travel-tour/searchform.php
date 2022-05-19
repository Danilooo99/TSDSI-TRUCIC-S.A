<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label>
        <span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'travel-tour' ) ?></span>
        <input type="search" class="search-field" placeholder="<?php echo esc_attr_e( 'Search &hellip;', 'travel-tour' ) ?>"
            value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_e( 'Search for:', 'travel-tour' ) ?>" />
    </label>
    <input type="submit" class="search-submit" value="<?php echo esc_attr_e( 'Search', 'travel-tour' ) ?>" />
</form>	