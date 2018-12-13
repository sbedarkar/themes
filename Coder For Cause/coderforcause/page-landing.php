 
 <?php 
	
/*
	Template Name: Landing Page
*/
	
get_header(); ?>

 
<div class="header-container text-center background-image" style="background-image: url(<?php header_image(); ?>);">

  <div class="backg w3-display-left w3-text-black ">
    <span class="w3-jumbo w3-hide-small">Coder For Cause</span><br>
    <!--<span class="landing-text w3-large w3-hide-small">Non profit software engineering for other non-profit organizations</span><br>-->
    <span class="w3-xlarge w3-hide-large ">Coder For Cause</span>
    <p><a href="#about" class="w3-button w3-white w3-padding-large w3-large w3-margin-top w3-opacity w3-hover-opacity-off">Learn more and start today</a></p>
  </div> 
  <div class="w3-display-bottomleft w3-text-grey w3-large" style="padding:24px 48px">
    <a href="https://www.facebook.com/"><i class="fa fa-facebook-official w3-hover-opacity"></i></a>
    <i class="fa fa-instagram w3-hover-opacity"></i>
    <a href="https://www.linkedin.com/in/swati-gore-379100148/"><i class="fa fa-linkedin w3-hover-opacity"></i></a>
  </div>
</div>

<!-- About Section -->
<div class="w3-container" style="padding:128px 16px" id="about">
  <h3 class="w3-center">ABOUT THE COMPANY</h3>
  <p class="w3-center w3-large">Non profit software engineering for other non-profit organizations</p>
  <div class="w3-row-padding w3-center" style="margin-top:64px">
    
    <div class="w3-third">
      <i class="fa fa-heart w3-margin-bottom w3-jumbo"></i>
      <p class="w3-large">Passion</p>
      <p style="text-align: justify;"><?php 
	

	$lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Passion');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></p>
    </div>
    <div class="w3-third">
      <i class="fa fa-plane w3-margin-bottom w3-jumbo"></i>
      <p class="w3-large">Mission</p>
      <p><?php 
	

	$lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Mission');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></p>
    </div>
    <div class="w3-third">
      <i class="fa fa-cog w3-margin-bottom w3-jumbo"></i>
      <p class="w3-large">Support</p>
      <p><?php 
	

	 $lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Support');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></p>
    </div>
  </div>
</div>

<?php get_footer(); ?>