<?php
/*
 * queue_viewer.php
 *
 * Displays the queue viewer
 *
 */
session_start();

// Are we authorized to view this page?
if ( ! isset($_SESSION['id']) )
{
  header('Location: index.php');
  exit();
} 

if ( $_SESSION['userlevel'] < 2 )
{
  header('Location: index.php');
  exit();
} 

define( 'DEBUG', true );

include 'config.php';
include 'db.php';

// Start displaying page
$page_title = "Queue Viewer";
$js = 'js/queue_viewer.js';
$css = 'css/queue_viewer.css';
include 'top.php';
include 'links.php';
?>
<!-- Begin page content -->
<div id='content'>

  <h1 class="title">Queue Viewer</h1>
  <!-- Place page content here -->
  <?php echo page_content();
        echo page_content2();  ?>

</div>

<?php
include 'bottom.php';
exit();

// A function to generate the page content using the limsv3 database
function page_content()
{
  $content = "<h3>LIMS v3 Queue</h3>\n";

  $query  = "SELECT startTime, queueStatus, lastMessage, updateTime, " .
            "investigatorGUID, submitterGUID, clusterName, method, runID " .
            "FROM HPCAnalysisResult r, HPCAnalysisRequest q, experiment " .
            "WHERE ( ( queueStatus = 'queued' ) || " .
            "        ( queueStatus = 'running' ) ) " .
            "AND r.HPCAnalysisRequestID = q.HPCAnalysisRequestID " .
            "AND q.experimentID = experiment.experimentID " .
            "ORDER BY startTime ";
  $result = mysql_query( $query )
            or die( "Query failed : $query<br />" . mysql_error());

  if ( mysql_num_rows( $result ) == 0 )
    $content .= "<p>No jobs are currently queued</p>\n";

  else
  {
    $table  = "<table>\n";
	  $table .= "<tr><td colspan='5' class='decoration'><hr/></td></tr>\n";

    while( $row = mysql_fetch_array( $result ) )
    {
      foreach ( $row as $key => $value )
      {
        $$key = (empty($value)) ? "" : html_entity_decode(stripslashes( nl2br($value)) );
      }

      $query2  = "SELECT email FROM people " .
                 "WHERE personGUID = '$investigatorGUID' ";
      $result2 = mysql_query( $query2 )
                 or die( "Query failed : $query2<br />" . mysql_error());
      list( $email ) = mysql_fetch_array( $result2 );

      if ( $investigatorGUID != $submitterGUID )
      {
        $query2  = "SELECT email FROM people " .
                   "WHERE personGUID = '$submitterGUID' ";
        $result2 = mysql_query( $query2 )
                   or die( "Query failed : $query2<br />" . mysql_error());
        list( $submitterEmail ) = mysql_fetch_array( $result2 );
        $email .= " ($submitterEmail)";
      }

      $table .= "<tr><th>Run ID:</th>\n" .
                "<td colspan='3'>$runID</td>\n" .
                "<td rowspan='6'>\n" .
                display_buttons() .
                "</td></tr>\n";

      $table .= <<<HTML
      <tr><th>Owner:</th>
          <td colspan='3'>$email</td></tr>

      <tr><th>Last message:</th>
          <td colspan='3'>$lastMessage</td></tr>

      <tr><th>Status:</th>
          <td class='$queueStatus'>$queueStatus</td>
          <th>Analysis Type:</th>
          <td>$method</td></tr>

      <tr><th>Started on:</th>
          <td>$startTime</td>
          <th rowspan='2'>Running on:</th>
          <td rowspan='2'>$clusterName</td></tr>

      <tr><th>Last Updated:</th>
          <td>$updateTime</td></tr>

	    <tr><td colspan='5' class='decoration'><hr/></td></tr>
HTML;
    }

    $table .= "</table>\n";
  }

  $content .= $table;

  return $content;
}

// A function to optionally generate delete buttons
function display_buttons()
{
  $buttons = "";

  return $buttons;
}

// A function to generate page content using lims2 methods
function page_content2()
{
  $content = "<h3>LIMS v2 Queue</h2>\n";

  exec("/share/apps64/ultrascan/bin64/mpi_status", $aData, $iRet );

  // Print queue status timestamp
  $content .= "<h5>$aData[0]:\n" .
              "  <input type='button' value='Refresh'\n" .
              "  onclick='window.location.href=window.location.href;' /></h5>\n";

  // Check if there are any jobs in the queue
  if (sizeof( $aData ) == 3 and $aData[2] == "No jobs are currently queued.")
  {
    $content .= "<p>$aData[2]</p>";
  }

  // Check to see if a Delete button has been pressed
  else if (isset($_POST['delete']))
  {
    $jobid = $_POST['jobid'];
    $jobowner = $_POST['jobowner'];
    $jobtype = $_POST['jobtype'];
    $HPCAnalysisID = $_POST['HPCID'];

    // Double check user authorization
    if (is_authorized($jobowner))
    {
      if ($jobtype == "tigre")
        exec("/share/apps64/ultrascan/bin64/tigre_job_cancel $jobid");
      else if ($jobtype == "mpi")
        exec("/share/apps64/ultrascan/bin64/mpi_job_cancel $jobid");
      else
        ;                                         // unsupported job type

  $content .= <<<HTML
  <p>Your job has now been scheduled for deletion from the queue.
     The HPC data analysis queue will be updated within the next
     couple of minutes, and your job will then be deleted. You will
     receive a message in your e-mail when the job has been cancelled.</p>

  <p>You can now return to the 
     <a href='$_SERVER[PHP_SELF]'>HPC Data Analysis Queue Viewer</a>
     and refresh the view in a couple of minutes to obtain the updated 
     queue.</p>
HTML;

    }
  }

  // No other tasks at hand --- just display the queue
  else
  {
    $content .= "<table>\n";
    $content .= "<tr><td colspan='5' class='decoration'><hr/></td></tr>\n";
    for( $i = 2; $i < sizeof( $aData ); $i++ ) 
    {
      unset( $fields );
      unset( $jobdata );

      $k		= $i - 1;
      $fields = explode( " ", $aData[$i] );

      for ( $j = 0; $j < sizeof( $fields ); $j++ )
      {
        // Eliminate empty fields to get fields into 
        // the proper key numbering
        if ( ($fields[$j] != "") && ($fields[$j] != ":") )
        {
          $jobdata[] = $fields[$j];
        }
      }

      // Calculate MC iterations
      $iterations = "";
      if ( isset($jobdata[15]) )
      {
        $iterations = " (current MC iteration: " . ( $jobdata[15]+1 ) . ")";
      }

      $content .= "<tr><th>Name:</th>\n" .
                  "<td colspan='3'>$jobdata[8]</td>\n" .
                  "<td rowspan='5'>\n" .
                  display_buttons2($jobdata) .
                  "</td></tr>\n";

      $content .= "<tr><th>Owner:</th>" .
                  "<td colspan='3'>$jobdata[7]</td></tr>\n";

      $content .= "<tr><th>Job $k:</th>" .
                  "<td colspan='3'>$jobdata[0]$iterations</td>\n" .
                  "</tr>\n";
      
        if ($jobdata[14] == "Active" ||
      $jobdata[14] == "ACTIVE" )
        {
           $content .= "<tr><th>Status:</th>" .
                       "<td bgcolor='#47ff47'>$jobdata[14]</td>\n" .
                       "<th>Analysis Type:</th>" .
                       "<td>$jobdata[9]</td></tr>\n";
        }
        else if ($jobdata[14] == "Failed" ||
           $jobdata[14] == "FAILED")
        {
           $content .= "<tr><th>Status:</th>" .
                       "<td bgcolor='#ff4747'>$jobdata[14]</td>\n" .
                       "<th>Analysis Type:</th>" .
                       "<td>$jobdata[9]</td></tr>\n";
        }
        else if ($jobdata[14] == "Pending" ||
           $jobdata[14] == "PENDING" )
        {
           $content .= "<tr><th>Status:</th>" .
                       "<td bgcolor='#8888ff'>$jobdata[14]</td>\n" .
                       "<th>Analysis Type:</th>" .
                       "<td>$jobdata[9]</td></tr>\n";
        }
        else if ($jobdata[14] == "Unsubmitted")
        {
           $content .= "<tr><th>Status:</th>" .
                       "<td bgcolor='#ffff47'>$jobdata[14]</td>\n" .
                       "<th>Analysis Type:</th>" .
                       "<td>$jobdata[9]</td></tr>\n";
        }
        else
        {
           $content .= "<tr><th>Status:</th>" .
                       "<td>$jobdata[14]</td>\n" .
                       "<th>Analysis Type:</th>" .
                       "<td>$jobdata[9]</td></tr>\n";
        }
      
      $content .= "<tr><th>Submitted on:</th>" .
                  "<td>$jobdata[4], at $jobdata[5]</td>\n" .
                  "<th>Running on:</th>" .
                  "<td>$jobdata[6]</td></tr>\n";
    
      $content .= "<tr><td colspan='5' class='decoration'><hr/></td></tr>\n";
    }
    $content .= "</table>\n";

  }

  if (sizeof( $aData ) != 3 or $aData[2] != "No jobs are currently queued.")
  {
    // Print queue status timestamp a second time, if there are jobs listed
    $content .= "<h5>$aData[0]:\n" .
                "  <input type='button' value='Refresh'\n" .
                "  onclick='window.location.href=window.location.href;' /></h5>\n";
  }

  return $content;
}

// If current user is authorized to delete this job, display
//  a delete button
function display_buttons2($jobdata)
{
  $jobowner      = $jobdata[7];
  $cluster       = $jobdata[6];
  $jobid         = $jobdata[0];
  $jobtype       = $jobdata[3];
  $HPCAnalysisID = $jobdata[2];
  $gc_file       = $jobdata[10];

  $content       = '';

  $lines = file( "/share/apps64/ultrascan/etc/queue_status_detail" );
  $moreinfo = '';
  foreach ( $lines as $line )
  {
    $detail = explode( ' ', $line );
    if ( $detail[0] == $jobid )
    {
      $moreinfo = substr( $line, strpos( $line, ' ' ) );
      break;
    }
  }

  $moreinfo_box  = "";
  if ( ! empty( $moreinfo ) )
  {
    $moreinfo_box = <<<HTML
      <div id='info$jobid' class='more_info'>
        <div class='moreinfo_hdr'>Job $jobid Info<br />
          <hr /></div>
        $moreinfo
      </div>
HTML;
  }

  if (is_authorized($jobowner))
  {
    // Button to delete current job from the queue
    $content .= "<form action='$_SERVER[PHP_SELF]' method='post'>\n" .
                "  <input type='hidden' name='jobid' value='$jobid' />\n" .
                "  <input type='hidden' name='jobtype' value='$jobtype' />\n" .
                "  <input type='hidden' name='jobowner' value='$jobowner' />\n" .
                "  <input type='hidden' name='HPCID' value='$HPCAnalysisID' />\n" .
                "  <input type='submit' name='delete' value='Delete' />\n" .
                "</form>\n";
  }

  // Button to show more info, if it exists
  if ( !empty($moreinfo_box) )
  {
    $content .= <<<HTML
    $moreinfo_box
    <button id='more_info$jobid' onclick='return show_info( $jobid );'>
            More Info</button>
HTML;
  }

  return $content ;
}

// Figure out if current user is authorized to delete this job
function is_authorized($jobowner)
{
  $authorized = false;

  // $jobowner could have multiple emails in it
  $pos = strpos( $jobowner, $_SESSION['submitter_email'] );

  if ( ($_SESSION['userlevel'] >= 2) &&
       ( $pos !== false ) )
    $authorized = true;

  else if ($_SESSION['userlevel'] == 4)
    $authorized = true;

  return ($authorized);
}

?>
