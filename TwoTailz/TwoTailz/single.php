<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="section">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

<header>
<div class="headline"><?php the_title(); ?></div>
</header>

<section>


<?php if( get_field('HTimgL') ): ?>
<img class="blog-images" src="<?php the_field('HTimgL'); ?>" />
<?php endif; ?>

<?php if( get_field('HTimgR') ): ?>
<img class="blog-images" src="<?php the_field('HTimgR'); ?>" />
<?php endif; ?>

<?php if( get_field('FMimg') ): ?>
<img class="blog-images" src="<?php the_field('FMimg'); ?>" />
<?php endif; ?>

<?php if( get_field('content_type') ): ?>
<div class="emph" style="color:#81be41!important; margin-top:5px;"><?php the_field('content_type'); ?></div>
<?php endif; ?>

<?php if ( get_field('rescued') && (0 < strlen(trim(the_field('rescued')))) ): ?>
<div style="margin-top:5px;"><b>Rescued:</b> <?php the_field('rescued'); ?></div>
<?php endif; ?>

<?php if ( get_field('crossed_rainbow_bridge') && (0 < strlen(trim(the_field('crossed_rainbow_bridge')))) ): ?>
<div style="margin-top:5px;"><b>Crossed The Rainbow Bridge:</b> <?php the_field('crossed_rainbow_bridge'); ?></div>
<?php endif; ?>

<?php if( get_field('fm_content_type') ): ?>
<div style="margin-top:5px;"><?php the_field('fm_content_type'); ?></div>
<?php endif; ?>

<div id="single-post-content"><?php the_content(); ?></div>

</section>

</article>
</div>

	<?php endwhile; else: ?>

		<div class="section">
			<article>
				<p><?php _e('Sorry, no posts matched your criteria.', 'h5'); ?></p>
			</article>
		</div>

	<?php endif; ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
