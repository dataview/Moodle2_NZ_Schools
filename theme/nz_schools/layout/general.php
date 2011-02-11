<?php
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">
<div class="page-effect">
<!-- start of header -->
<div id="page-header" class="clearfix">
  <div id="page-header-wrapper">
    <div id="header-wrap">
		<div id="header-container">
			<div id="header">		
				<div class="school-logo"><img class="unitPng" src="<?php echo $CFG->wwwroot?>/local/nzschools/logo.php" alt="" id="logoimage" /></div>
                <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
				<div id="headermenu">
                <?php if (isloggedin()) {
					echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>55)), array('class'=>'userpic'));
				} else {
					echo html_writer::end_tag('');;
				} ?> 				
				<?php
                                echo $OUTPUT->login_info();
                                echo html_writer::empty_tag('br');
                                echo $OUTPUT->lang_menu();
			    ?>
				</div>
			
			</div>
		</div>
	</div>
  </div>
</div>    
<!-- end of header -->

<!-- start of navbar -->

    <?php if ($hasnavbar) { ?>
	    <div class="navbar clearfix">
          <div class="nav-effect">
    	    <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
            <div class="navbutton"> <?php echo $PAGE->button; ?></div>
          </div>
        </div>
    <?php } ?>
                    
<!-- end of navbar -->

<!-- start of main content wraps -->
<div class="navbar blank clearfix"><div class="nav-effect">&nbsp;</div></div>
<div id="wrapper" class="clearfix">
	<div id="ie6-container-wrap">
		<div id="outercontainer">
			<div id="container">
				<div id="innercontainer">
	
					
	
	<!-- start OF moodle CONTENT -->
				<div id="page-content">
        			<div id="region-main-box">
            			<div id="region-post-box">
            
                				<div id="region-main-wrap">
                    				<div id="region-main">
                        				<div class="region-content">
         
                            			<?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                        				</div>
                    				</div>
                				</div>
                
                	<?php if ($hassidepre) { ?>
               		<div id="region-pre" class="block-region">
                    	<div class="region-content">
                   
        
                        	<?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    	</div>
                	</div>
                	<?php } ?>
                
                	<?php if ($hassidepost) { ?>
                 	<div id="region-post" class="block-region">
                    	<div class="region-content">
                   
                        	<?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    	</div>
                	</div>
                	<?php } ?>
                
            			</div>
        			</div>
   				 </div>
    <!-- END OF CONTENT --> 
      
     			 <div id="jcontrols_bottom">
      			</div>
	
	<!-- Containers end div above clears both -->
				</div>
			</div>
		</div>	
	</div>
    <div class="clearer"></div>
</div>
</div>
<!-- START OF FOOTER -->
	<div id="page-footer" class="clearfix">
    <div id="footer-wrap">
		<div id="footer-container">
			<div id="footer">
		
			 <?php if ($hasfooter) { 
        		echo $OUTPUT->login_info();
       			echo $OUTPUT->home_link();
        		echo $OUTPUT->standard_footer_html();
        		echo "</div>";
       			} ?>
         
    			<?php if ($hasfooter) { ?>
    			<div class="moodledocs">
      				<?php echo page_doc_link(get_string('moodledocslink')) ?>
       			</div>
    			<?php } ?>
        
			</div>
		</div>
	</div>
	</div>

</div>


<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>