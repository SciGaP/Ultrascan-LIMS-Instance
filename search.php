<?php
/*
 * search.php
 *
 * A place to display search results
 *
 */
session_start();

include 'config.php';

// Start displaying page
$page_title = "Search Results";
$page_css   = "page_search";
$nolinks    = true;
include 'header.php';
?>
<!-- Begin page content -->
<div id='content_search'>

  <h1 class="title">Search Results</h1>
  <!-- Place page content here -->
 
  <div id="cse" style="width: 70%;">Loading</div>
  <script src="http://www.google.com/jsapi" type="text/javascript"></script>
  <script type="text/javascript"> 
    function parseQueryFromUrl () {
      var queryParamName = "q";
      var search = window.location.search.substr(1);
      var parts = search.split('&');
      for (var i = 0; i < parts.length; i++) {
        var keyvaluepair = parts[i].split('=');
        if (decodeURIComponent(keyvaluepair[0]) == queryParamName) {
          return decodeURIComponent(keyvaluepair[1].replace(/\+/g, ' '));
        }
      }
      return '';
    }
    google.load('search', '1', {language : 'en', style : google.loader.themes.MINIMALIST});
    google.setOnLoadCallback(function() {
      var customSearchControl = new google.search.CustomSearchControl('007201445830912588415:jg05a0rix7y');
      customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
      customSearchControl.draw('cse');
      var queryFromUrl = parseQueryFromUrl();
      if (queryFromUrl) {
        customSearchControl.execute(queryFromUrl);
      }
    }, true);
  </script>
    
</div>

<?php
//include 'footer.php';
exit();
?>
