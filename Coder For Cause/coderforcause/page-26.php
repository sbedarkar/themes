
<?php get_header(); ?>

<div class="container">
<!-- <h1>This is the index</h1> -->

<div class="jumbotron ">
<div class="row">
     <div class="col-xs-12 col-md-4 polaroid "Style="text-align:center;"> <i class="fa fa-heart w3-margin-bottom w3-jumbo text-center"></i><br><h1>Mission</h1><p><?php 
	

	$lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Mission');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></p></p></div>
     <div class="col-xs-12 col-md-4 polaroid" ><h1><span class="oi" data-glyph="heart"></span>Passion</h1><?php 
	

	$lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Passion');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></div>
     <div class="col-xs-12 col-md-4 polaroid " style="text-align: justify; " ><h1>Support</h1><?php 
	

	 $lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Support');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></div>
  </div>  

</div>

<?php 
get_footer(); 
?>