<?php
/**
 * Displays the content when the cover template is used.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">


	<div class="post-inner" id="post-inner">

		<div class="entry-content-custom">

		<?php
		the_content();
		?>

		</div><!-- .entry-content -->
		

	</div><!-- .post-inner -->



</article><!-- .post -->