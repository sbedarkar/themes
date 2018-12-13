<?php 
	
/*
	Template Name: Donation Page
*/
	
get_header(); ?>

 
<div class="header-container-donation text-center background-image" style="background-image: url(<?php header_image(); ?>);">
 <div class="w3-display-center w3-text-black polaroid  ">
    <span class="w3-jumbo w3-hide-small">Coder For Cause</span><br>
    <!--<span class="landing-text w3-large w3-hide-small">Non profit software engineering for other non-profit organizations</span><br>-->
    <span class="w3-xlarge w3-hide-large ">Coder For Cause</span>
    <p><a href="#about" class="w3-button w3-white w3-padding-large w3-large w3-margin-top w3-opacity w3-hover-opacity-off">Learn more and start today</a></p>
    <p><?php 
	

	 if( have_posts() ):
		
		while( have_posts() ): the_post(); 
	?>

		<?php 

		get_template_part('content',get_post_format()) ;
		?>

		<?php 
	endwhile;
		
	endif;
			
	?>
    </p>
  </div> 
  
</div>



<?php get_footer(); ?>