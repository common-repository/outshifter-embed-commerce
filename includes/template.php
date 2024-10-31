<?php
/*
 * Template Name: Shop Layout
 * Template Post Type: post, page
 * Description: A Page Template with a darker design.
 */

get_header();
?>

<main id="site-content" role="main">

	<?php

if ( have_posts() ) {

    $i = 0;

    while ( have_posts() ) {
        $i++;
        if ( $i > 1 ) {
            echo '<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />';
        }
        the_post();

        get_template_part(require_once plugin_dir_path( __FILE__ ) . '/layout.php'        , get_post_type() );

    }
} 
?>

</main><!-- #site-content -->

<?php get_footer(); ?>