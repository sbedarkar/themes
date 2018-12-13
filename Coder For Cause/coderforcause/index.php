
<?php get_header(); ?>



<!-- w3-content defines a container for fixed size centered content, 
and is wrapped around the whole page content, except for the footer in this example -->
<div class="w3-content" style="max-width:1400px">

<!-- Header -->
<header class="w3-container w3-center w3-padding-32"> 
  <h1><b><?php the_title(); ?></b></h1>
</header>

<!-- Grid -->
<div class="w3-row">

<!-- Blog entries -->
<div class="w3-col l8 s12">
  <!-- Blog entry -->
  <div class="w3-card-4 w3-margin w3-white">
    
    <!--<img src="http://box5701.temp.domains/~coderfo2/wp-content/uploads/2018/11/Passion.jpg" alt="Nature" style="width:100%">-->
    <div class="header-image-height background-image" style="background-image: url(<?php header_image(); ?>);"></div>

    <div class="w3-container">
      <?php 
	

	 if( have_posts() ):
		
		while( have_posts() ): the_post(); 
	?>

		<?php 

		get_template_part('content',get_post_format()) ;
		?>

		<?php 
	endwhile;
		
	endif;
			
	?></p>
    
    </div>
  </div>
  <hr>

</div>

<!-- Introduction menu -->
<div class="w3-col l4">
  <!-- About Card -->
  <div class="w3-card w3-margin w3-margin-top ">
<div class="margin-left-image" >
    <?php
			
		//PRINT ONLY SWATI CATEGORY
		$lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Photo');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();
				
		?>
</div>
  
    <div class="w3-container w3-white">
      <h4><b>Swati Gore</b></h4>
      <p><i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-teal"></i>Full Stack Web Developer</p>
          <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-teal"></i>Marietta, GA</p>
          <p><i class="fa fa-envelope fa-fw w3-margin-right w3-large w3-text-teal"></i>swati.gore2feb@gmail.com</p>
          <p><i class="fa fa-phone fa-fw w3-margin-right w3-large w3-text-teal"></i>312-810-9605</p>
    </div>
  </div><hr>
  
  
  
<!-- END Introduction Menu -->
</div>

<!-- END GRID -->
</div><br>

<!-- END w3-content -->
</div>

<?php 
get_footer(); 
?>