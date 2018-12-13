<?php get_header(); ?>

       
   

<div class="container">
<!-- <h1>This is the index</h1> -->

<div class="jumbotron ">

<!--<body class="w3-light-grey">-->

<!-- Page Container -->
<div class="w3-content w3-margin-top" style="max-width:1400px; boarder:5px solid red;">

  <!-- The Grid -->
  <div class="w3-row-padding">
  
    <!-- Left Column -->
    <div class="w3-third">
    
      <div class="w3-white w3-text-grey w3-card-4">
        <div class="w3-display-container">
        <?php
			
		//PRINT ONLY SWATI CATEGORY
		$lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=swati');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();
				
		?>
     
          <!--<div class="w3-display-topleft w3-container w3-text-black">-->
          <!--  <h2>Swati Gore</h2>-->
          <!--</div>-->
        </div>
        <div class="w3-container">
          <p><i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-teal"></i>Full Stack Web Developer</p>
          <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-teal"></i>Marietta, GA</p>
          <p><i class="fa fa-envelope fa-fw w3-margin-right w3-large w3-text-teal"></i>swati.gore2feb@gmail.com</p>
          <p><i class="fa fa-phone fa-fw w3-margin-right w3-large w3-text-teal"></i>312-810-9605</p>
          <hr>

          <p class="w3-large"><b><i class="fa fa-asterisk fa-fw w3-margin-right w3-text-teal"></i>Skills</b></p>
          <p>Wordpress</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:90%">90%</div>
          </div>
          <p>PHP</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:80%">
              <div class="w3-center w3-text-white">80%</div>
            </div>
          </div>
          <p>HTML, CSS, Bootstrap</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:75%">75%</div>
          </div>
          <p>JavaScript</p>
          <div class="w3-light-grey w3-round-xlarge w3-small">
            <div class="w3-container w3-center w3-round-xlarge w3-teal" style="width:50%">50%</div>
          </div>
          <br>

          <p class="w3-large w3-text-theme"><b><i class="fa fa-certificate fa-fw w3-margin-right w3-text-teal"></i>Education</b></p>
          <p class="w3-text-teal">Master Degree</p>
          <p>University of Illinois at Chicago</p>
          <h6><i class="fa fa-calendar fa-fw w3-margin-right"></i>2011 - 2012</h6>
          
          <p class="w3-text-teal">Bachelor Degree</p>
          <p>University of Technology of Madhya Pradesh</p>
          <h6 ><i class="fa fa-calendar fa-fw w3-margin-right"></i>2005 - 2009</h6>
          
          <br>
          
         
          <br>
        </div>
      </div><br>
      

    <!-- End Left Column -->
    </div>

    <!-- Right Column -->
    <div class="w3-twothird">
    
      

     
      <div class="w3-container w3-card w3-white w3-margin-bottom">
        <h2 class="w3-text-grey w3-padding-16">Swati Gore</h2>
   
        <div class="w3-container">
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
			
	?></p>
          <!--<hr>-->
        </div>
        <div class="w3-container">
          <h5 class="w3-opacity"><b>Passion</b></h5>
          <p><?php 
	

	 $lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Passion');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></p><br>
        </div>
        <div class="w3-container">
          <h5 class="w3-opacity"><b>Mission</b></h5>
          <p><?php 
	

	 $lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Mission');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></p><br>
        </div>
        <div class="w3-container">
          <h5 class="w3-opacity"><b>Support</b></h5>
          <p><?php 
	

	 $lastBlog = new WP_Query('type=post&posts_per_page=-1&category_name=Support');
			
		if( $lastBlog->have_posts() ):
			
			while( $lastBlog->have_posts() ): $lastBlog->the_post(); ?>
				
				<?php get_template_part('content',get_post_format()); ?>
			
			<?php endwhile;
			
		endif;
		
		wp_reset_postdata();

			
	?></p><br>
        </div>
      </div>

    <!-- End Right Column -->
    </div>
    
  <!-- End Grid -->
  </div>
  
  <!-- End Page Container -->
</div>

<!--<footer class="w3-container w3-teal w3-center w3-margin-top">-->
<!--  <p>Find me on social media.</p>-->
<!--  <i class="fa fa-facebook-official w3-hover-opacity"></i>-->
<!--  <i class="fa fa-instagram w3-hover-opacity"></i>-->
<!--  <i class="fa fa-snapchat w3-hover-opacity"></i>-->
<!--  <i class="fa fa-pinterest-p w3-hover-opacity"></i>-->
<!--  <i class="fa fa-twitter w3-hover-opacity"></i>-->
<!--  <i class="fa fa-linkedin w3-hover-opacity"></i>-->
<!--  <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>-->
<!--</footer>-->

</div>
<?php 
get_footer(); 
?>