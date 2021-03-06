<?php
/*
 * links.php
 *
 * Include file that contains links
 *  Needs session_start(), config.php
 *
 */

$userlevel = ( isset( $_SESSION['userlevel'] ) ) ? $_SESSION['userlevel'] : -1;

$projects_menu = <<<HTML
  <h4>Project</h4>
  <a href='https://$org_site/view_projects.php'>Projects</a>
  <a href='https://$org_site/edit_images.php'>Images</a>
  <a href='https://$org_site/view_reports.php'>Reports</a>
  <a href='https://$org_site/data_sharing.php'>Sharing</a>

HTML;

$analysis_menu = <<<HTML
  <h4>Analysis</h4>
  <a href='https://$org_site/queue_setup_1.php'>Queue Setup</a>
  <a href='https://$org_site/2DSA_1.php'>2DSA Analysis</a>
  <a href='https://$org_site/2DSA-CG_1.php'>2DSA Custom Grid</a>
  <a href='https://$org_site/GA_1.php'>GA Analysis</a>
  <a href='https://$org_site/DMGA_1.php'>Discrete GA</a>
  <a href='https://$org_site/PCSA_1.php'>PCSA Analysis</a>
  <a href='https://$org_site/runID_info.php'>RunID Info</a></li>

HTML;

$monitor_menu = <<<HTML
  <h4>Status Monitor</h4>
  <a href='https://$org_site/queue_viewer.php'>Queue Status</a>
  <a href='http://grid.uthscsa.edu'>Cluster Status</a>

HTML;

$general_menu = <<<HTML
  <h4>General</h4>
  <a href='https://$org_site/profile.php?edit=12'>Change My Info</a>
  <a href='http://$org_site/view_database_info.php'>Database Login Info</a>
  <a href="partners.php">Partners</a>
  <a href='contacts.php'>Contacts</a>
  <a href='mailto:$admin_email'>Webmaster</a>
  <a href='data_security.php'>Data Security</a>
  <a href='http://$org_site/logout.php'>Logout</a>

HTML;

$general_menu_1 = <<<HTML
  <h4>General</h4>
  <a href='https://$org_site/profile.php?edit=12'>Change My Info</a>
  <a href="partners.php">Partners</a>
  <a href='contacts.php'>Contacts</a>
  <a href='mailto:$admin_email'>Webmaster</a>
  <a href='data_security.php'>Data Security</a>
  <a href='http://$org_site/logout.php'>Logout</a>

HTML;

if ( $userlevel == 5 )  // level 5 = super admin ( developer )
{
  $sidebar_menu = <<<HTML
  <h4>Navigation</h4>
  <a href="http://$org_site/index.php">Welcome!</a>
  <a href='https://$org_site/admin_links.php'>Admin Info</a>
  $projects_menu
  $analysis_menu
  $monitor_menu
  $general_menu

HTML;
}

else if ( $userlevel == 4 )  // userlevel 4 = admin
{
  $sidebar_menu = <<<HTML
  <h4>Navigation</h4>
  <a href="http://$org_site/index.php">Welcome!</a>
  <a href='https://$org_site/admin_links.php'>Admin Info</a>
  $projects_menu
  $analysis_menu
  $monitor_menu
  $general_menu

HTML;
}

else if ( $userlevel == 3 )  // userlevel 3 = superuser
{
  $sidebar_menu = <<<HTML
  <h4>Navigation</h4>
  <a href="http://$org_site/index.php">Welcome!</a>
  <a href='https://$org_site/admin_links.php'>Admin Info</a>
  $projects_menu
  $analysis_menu
  $monitor_menu
  $general_menu

HTML;
}

else if ( $userlevel == 2 )  // userlevel 2 = Data analyst
{
  $sidebar_menu = <<<HTML
  <h4>Navigation</h4>
  <a href="http://$org_site/index.php">Welcome!</a>
  $projects_menu
  $analysis_menu
  $monitor_menu
  $general_menu

HTML;
}

else if ( $userlevel == 1 )  // level 1 = privileged user
{
  $sidebar_menu = <<<HTML
  <h4>Navigation</h4>
  <a href="http://$org_site/index.php">Welcome!</a>
  $projects_menu
  $general_menu_1

HTML;
}

else if ( $userlevel == 0 )  // level 0 = regular user
{
  $sidebar_menu = <<<HTML
  <h4>Navigation</h4>
  <a href="http://$org_site/index.php">Welcome!</a>
  $projects_menu
  $general_menu_1

HTML;
}

else // not logged in
{
  $sidebar_menu = <<<HTML
  <a href="http://$org_site/index.php">Welcome!</a>
  <a href="partners.php">Partners</a>
  <a href='contacts.php'>Contacts</a>
  <a href='mailto:$admin_email'>Webmaster</a>
  <a href='data_security.php'>Data Security</a>
  <a href='https://$org_site/login.php'>Login</a>

HTML;
}

echo<<<HTML
      
<div id='sidebar'>

  $sidebar_menu

  <!-- A spacer -->
  <!--div style='padding-bottom:20em;'></div-->

</div>
HTML;
?>
