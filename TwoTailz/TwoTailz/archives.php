<?php 
	/*
		Template Name: Archives
	*/ 
?>
<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="section">
<article id="post-<?php the_ID(); ?>">
<header>
<div class="head"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></div>
</header>

<section>
<?php if( get_field('HTimgL') ): ?>
<img style="max-width:49%;margin-right:2%;" src="<?php the_field('HTimgL'); ?>" />
<?php endif; ?>
<?php if( get_field('HTimgR') ): ?>
<img style="max-width:49%;" src="<?php the_field('HTimgR'); ?>" />
<?php endif; ?>
<?php the_content(); ?>
</section>
<footer>
<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
</footer>
    
</article>
</div>

<?php endwhile; else: ?>

<div class="section">
<article>
<p><?php _e('Sorry, no posts matched your criteria.', 'h5'); ?></p>
</article>
</div>

<?php endif; ?>

<?php get_footer(); ?>