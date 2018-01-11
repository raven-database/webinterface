<?php
  session_start();
  require_once('atlas_header.php'); //The header
  require_once('atlas_fns.php'); //All the routines
  d_metadata_header(); //Display header
  $phpscript = "metadata.php";
?>
  <div id="metamenu">
	<ul>
		<li ><a class="active" href="metadata.php">MetaData Information</a></li>
		<li ><a href="sequence.php">Sequencing Information</a></li>
	</ul>
</div>
  <div class="explain"><p>View bio-data of the RNA-Seq libraries processed and status information.</p></div>
<?php
  /* Tables required from the database */
  $table = "BirdLibraries";
  $statustable = "TheMetadata";
	$genestatus = "GenesSummary";
	$variantstatus = "VariantSummary";
?>
<?php
  $query = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
  $result = $db_conn->query($query);
  // create array of primary keys
  $primary_key = null;
  while ($row = $result->fetch_assoc()) {
    $primary_key = $row['Column_name'];
  }
  if (!$primary_key) {
    echo "<div><strong>NO PRIMARY KEY!\nFUNCTIONS WILL NOT WORK AS EXPECTED!</strong></div>";
  }
	$query = "select $table.libraryid, $table.birdid, $table.species, $table.line, $table.tissue, $table.method, $table.date, $table.notes, $statustable.status, $genestatus.genestatus, $genestatus.countstatus, $variantstatus.status from $table left outer join $statustable on $table.libraryid = $statustable.libraryid left outer join $genestatus on $genestatus.libraryid = $table.libraryid left outer join $variantstatus on $variantstatus.libraryid = $table.libraryid ";
?>
<?php
  //create query for DB display
  if (!empty($_GET['libs'])) {
    // if the sort option was used
    $_SESSION['num_recs'] = "all";

    $terms = explode(",", $_GET['libs']);
    $is_term = false;
    foreach ($terms as $term) {
      if (trim($term) != "") {
        $is_term = true;
      }
    }
    $_SESSION['select'] = $terms;
    $_SESSION['column'] = "libraryid";

		if ($is_term) {
        $query .= "WHERE ";
    }
    foreach ($_SESSION['select'] as $term) {
      if (trim($term) == "") {
        continue;
      }
      $query .= $table.".".$_SESSION['column'] . " =" . trim($term) . " OR ";
    }
    $query = rtrim($query, " OR ");
    $query .= " ORDER BY " . $table.".".$_SESSION['column'] . " " . $_SESSION['dir'];

    $result = $db_conn->query($query);
    $num_total_result = $result->num_rows;
    if ($_SESSION['num_recs'] != "all") {
      $query .= " limit " . $_SESSION['num_recs'];
    }
  }
  elseif (!empty($_REQUEST['order'])) {
    // if the sort option was used
    $_SESSION['sort'] = $_POST['sort'];
    $_SESSION['dir'] = $_POST['dir'];
    $_SESSION['num_recs'] = $_POST['num_recs'];

    $terms = explode(",", $_POST['search']);
    $is_term = false;
    foreach ($terms as $term) {
      if (trim($term) != "") {
        $is_term = true;
      }
    }
    $_SESSION['select'] = $terms;
    $_SESSION['column'] = $_POST['column'];
    $_SESSION['status'] = $_POST['rnull'];
		$_SESSION['gstatus'] = $_POST['gnull'];
		$_SESSION['cstatus'] = $_POST['cnull'];
		$_SESSION['vstatus'] = $_POST['vnull'];

    if ($_SESSION['gstatus'] == "true"){ #gene
			$query .= " WHERE $genestatus.genestatus = ". '"done" ';
			if ($_SESSION['cstatus'] == "true"){ #g-count
				$query .= "AND $genestatus.countstatus = ". '"done" ';
			}
			if ($_SESSION['vstatus'] == "true"){ #g-variant
				$query .= "AND $variantstatus.status = ". '"done" ';
			}
			if ($_SESSION['status'] == "true"){ #g-default
				$query .= "AND $statustable.status = ". '"done" ';
			}
			if ($is_term) {
				$query .= "AND ";
			}
		} elseif ($_SESSION['cstatus'] == "true"){ #count
			$query .= " WHERE $genestatus.countstatus = ". '"done" ';
			if ($_SESSION['gstatus'] == "true"){ #c-gene
				$query .= "AND $genestatus.genestatus = ". '"done" ';
			}
			if ($_SESSION['vstatus'] == "true"){ #c-variant
				$query .= "AND $variantstatus.status = ". '"done" ';
			}
			if ($_SESSION['status'] == "true"){ #c-default
				$query .= "AND $statustable.status = ". '"done" ';
			}
			if ($is_term) {
				$query .= "AND ";
			}
		} elseif ($_SESSION['vstatus'] == "true"){ #variant
			$query .= " WHERE $variantstatus.status = ". '"done" ';
			if ($_SESSION['cstatus'] == "true"){ #v-count
				$query .= "AND $genestatus.countstatus = ". '"done" ';
			}
			if ($_SESSION['gstatus'] == "true"){ #v-gene
				$query .= "AND $genestatus.genestatus = ". '"done" ';
			}
			if ($_SESSION['status'] == "true"){ #v-default
				$query .= "AND $statustable.status = ". '"done" ';
			}
			if ($is_term) {
				$query .= "AND ";
			}
		} elseif ($_SESSION['status'] == "true"){ #default
			$query .= " WHERE $statustable.status = ". '"done" ';
			if ($_SESSION['cstatus'] == "true"){ #d-count
				$query .= "AND $genestatus.countstatus = ". '"done" ';
			}
			if ($_SESSION['gstatus'] == "true"){ #d-gene
				$query .= "AND $genestatus.genestatus = ". '"done" ';
			}
			if ($_SESSION['vstatus'] == "true"){ #d-variant
				$query .= "AND $variantstatus.status = ". '"done" ';
			}
			if ($is_term) {
				$query .= "AND ";
			}
		} else {
			if ($is_term) {
				$query .= "WHERE ";
			}
		}

    foreach ($_SESSION['select'] as $term) {
      if (trim($term) == "") {
        continue;
      }
      $query .= $table.".".$_SESSION['column'] . " LIKE '%" . trim($term) . "%' OR ";
    }
    $query = rtrim($query, " OR ");
    $query .= " ORDER BY " . $table.".".$_SESSION['sort'] . " " . $_SESSION['dir'];

    $result = $db_conn->query($query);
    $num_total_result = $result->num_rows;
    if ($_SESSION['num_recs'] != "all") {
      $query .= " limit " . $_SESSION['num_recs'];
    }
  } elseif (!empty($_SESSION['sort'])) {
    $is_term = false;
    foreach ($_SESSION['select'] as $term) {
      if (trim($term) != "") {
        $is_term = true;
      }
    }
    
		if ($_SESSION['status'] == "true"){
      $query .= " WHERE $statustable.status = ". '"done" ';
      if ($is_term) {
        $query .= "AND ";
      }
    }else {
      if ($is_term) {
        $query .= "WHERE ";
      }
    }
    foreach ($_SESSION['select'] as $term) {
      if (trim($term) == "") {
        continue;
      }
      $query .= $table.".".$_SESSION['column'] . " LIKE '%" . trim($term) . "%' OR ";
    }
    $query = rtrim($query, " OR ");
    $query .= " ORDER BY " . $table.".".$_SESSION['sort'] . " " . $_SESSION['dir'];

    $result = $db_conn->query($query);
    $num_total_result = $result->num_rows;

    if ($_SESSION['num_recs'] != "all") {
      $query .= " limit " . $_SESSION['num_recs'];
    }
  }
  $result = $db_conn->query($query);
  if ($db_conn->errno) {
    echo "<div>";
    echo "<span><strong>Error with query.$query</strong></span>";
    echo "<span><strong>Error number: </strong>$db_conn->errno</span>";
    echo "<span><strong>Error string: </strong>$db_conn->error</span>";
    echo "</div>";
  }
  $num_results = $result->num_rows;
  if (empty($_SESSION['sort'])) {
    $num_total_result = $num_results;
  }
?>
<?php
  echo '<table width="100%"><tr><td width="90%"><form action="'.$phpscript.'" method="post">';
?>
    <div class="question">
    <p class="pages"><span>Search for: </span>
    <input type="text" name="search" size="35" placeholder="Enter variable(s) separated by commas (,)"
	<?php
		if(!empty($db_conn))
		{
			echo 'value="' . implode(",", $_SESSION["select"]) .'"'; 
		} 
	?>
    />
    <span> in </span>
    <select name="column">
      <option value="libraryid">libraryid</option>
      <?php
        if (empty($_SESSION['column'])) {
          $_SESSION['libraryid'] = "libraryid";
        }
        if ($_SESSION['column'] == "birdid") {
          echo '<option selected value="birdid">birdid</option>';
        } else {
          echo '<option value="birdid">birdid</option>';
        }
        if ($_SESSION['column'] == "species") {
          echo '<option selected value="species">species</option>';
        } else {
          echo '<option value="species">species</option>';
        }
        if ($_SESSION['column'] == "line") {
          echo '<option selected value="line">line</option>';
        } else {
          echo '<option value="line">line</option>';
        }
        if ($_SESSION['column'] == "tissue") {
          echo '<option selected value="tissue">tissue</option>';
        } else {
          echo '<option value="tissue">tissue</option>';
        }
        if ($_SESSION['column'] == "method") {
          echo '<option selected value="method">method</option>';
        } else {
          echo '<option value="method">method</option>';
        }
        if ($_SESSION['column'] == "chipresult") {
          echo '<option selected value="chipresult">chipresult</option>';
        } else {
          echo '<option value="chip_result">chip_result</option>';
        }
        if ($_SESSION['column'] == "scientist") {
          echo '<option selected value="scientist">scientist</option>';
        } else {
          echo '<option value="scientist">scientist</option>';
        }
        if ($_SESSION['column'] == "notes") {
          echo '<option selected value="notes">notes</option>';
        } else {
          echo '<option value="notes">notes</option>';
        }
      ?> 
    </select></p><p class="pages">
    <span>Sort by:</span>
    <select name="sort">
      <option value="libraryid">libraryid</option>
      <?php
        if (empty($_SESSION['sort'])) {
          $_SESSION['libraryid'] = "libraryid";
        }
        if ($_SESSION['sort'] == "birdid") {
          echo '<option selected value="birdid">birdid</option>';
        } else {
          echo '<option value="birdid">birdid</option>';
        }
        if ($_SESSION['sort'] == "species") {
          echo '<option selected value="species">species</option>';
        } else {
          echo '<option value="species">species</option>';
        }
        if ($_SESSION['sort'] == "line") {
          echo '<option selected value="line">line</option>';
        } else {
          echo '<option value="line">line</option>';
        }
        if ($_SESSION['sort'] == "tissue") {
          echo '<option selected value="tissue">tissue</option>';
        } else {
          echo '<option value="tissue">tissue</option>';
        }
        if ($_SESSION['sort'] == "method") {
          echo '<option selected value="method">method</option>';
        } else {
          echo '<option value="method">method</option>';
        }
        if ($_SESSION['sort'] == "indexname") {
          echo '<option selected value="indexname">index</option>';
        } else {
          echo '<option value="indexname">indexname</option>';
        }
        if ($_SESSION['sort'] == "chipresult") {
          echo '<option selected value="chipresult">chipresult</option>';
        } else {
          echo '<option value="chip_result">chipresult</option>';
        }
        if ($_SESSION['sort'] == "scientist") {
          echo '<option selected value="scientist">scientist</option>';
        } else {
          echo '<option value="scientist">scientist</option>';
        }
        if ($_SESSION['sort'] == "date") {
          echo '<option selected value="date">date</option>';
        } else {
          echo '<option value="date">date</option>';
        }
        if ($_SESSION['sort'] == "notes") {
          echo '<option selected value="notes">notes</option>';
        } else {
          echo '<option value="notes">notes</option>';
        }
      ?> 
    </select> <!--if ascending or descending-->
    <select name="dir">
      <option value="asc">ascending</option>
      <?php
        if (empty($_SESSION['dir'])) {
          $_SESSION['asc'] = "asc";
        }
        if ($_SESSION['dir'] == "desc") {
          echo '<option selected value="desc">descending</option>';
        } else {
          echo '<option value="desc">descending</option>';
        }
      ?>
      </select>
    <span>and show</span>
    <select name="num_recs">
      <option value="10">10</option>
      <?php
        if (empty($_SESSION['num_recs'])) {
          $_SESSION['num_recs'] = "10";
        }
        if ($_SESSION['num_recs'] == "20") {
          echo '<option selected value="20">20</option>';
        } else {
          echo '<option value="20">20</option>';
        }
        if ($_SESSION['num_recs'] == "50") {
          echo '<option selected value="50">50</option>';
        } else {
          echo '<option value="50">50</option>';
        }
        if ($_SESSION['num_recs'] == "all") {
          echo '<option selected value="all">all</option>';
        } else {
          echo '<option value="all">all</option>';
        }
      ?> 
    </select>
    <span>records.</span></p><p class="pages">
    <span>View samples with mapping information:</span><input type="checkbox" name="rnull" value="true"><br>
		<span>View samples with gene expression information:</span><input type="checkbox" name="gnull" value="true"><br>
		<span>View samples with gene raw counts information:</span><input type="checkbox" name="cnull" value="true"><br>
		<span>View samples with variant information:</span><input type="checkbox" name="vnull" value="true"> 
    <br><center></venter><input type="submit" name="order" value="Go"/></p></div></center>
</form></tr></table>
<hr>
<?php
  if(!empty($db_conn) && (!empty($_POST['order']) || !empty($_GET['libs']) || !empty($_POST['meta_data']))) { //make sure an options is selected
    if ($num_total_result == 0){ //Cross check if libraries selected are in the database
      echo '<center>No results were found with your search criteria.<br>
      There are no "'.implode(",", $_SESSION["select"]).'" in "'.$_SESSION['column'].'".<center>';
    }else { //Provide download options
      echo '<div>';
      echo '<form action="' . $phpscript . '" method="post">';
      echo "<span>" . $num_results . " out of " . $num_total_result . " search results displayed. ";
      echo '<input type="submit" name="downloadvalues" value="Download Selected Values"/></span>
	    <input type="submit" name="downloadfpkm" value="Download FPKM  Values"/></span>
            <input type="submit" name="transfervalues" value="View Mapping Information"/></span><br>';
      meta_display($phpscript, $result, $primary_key);
      if(!empty($_POST['meta_data']) && isset($_POST['downloadvalues'])) { //If download Metadata
        foreach($_POST['meta_data'] as $check) {
          $dataline .= $check.",";
        }
        $dataline = rtrim($dataline, ",");
        $listfile = "metadata_".$explodedate.".txt";
        $output1 = "$base_path/OUTPUT/$listfile";
        $pquery = "perl ".$base_path."/SQLscripts/outputmetadata.pl -1 ".$dataline." -2 ".$output1." -m";
        shell_exec($pquery); 
        print("<script>location.href='results.php?file=$output1&name=metadata.txt'</script>");
      }
      elseif(!empty($_POST['meta_data']) && isset($_POST['downloadfpkm'])) { //If download fpkm
        foreach($_POST['meta_data'] as $check) {
          $dataline .= $check.",";
        }
        $dataline = rtrim($dataline, ",");
        $output = "$base_path/OUTPUT/fpkm_".$explodedate;
	$output1 = "$base_path/OUTPUT/fpkm_".$explodedate.".txt";
        $pquery = "perl ".$base_path."/SQLscripts/outputgenequery.pl -1 ".$dataline." -2 ".$output." -3 fpkm";
        #echo $pquery;
	shell_exec($pquery); 
        print("<script>location.href='results.php?file=$output1&name=fpkm.txt'</script>");
      }
			elseif(!empty($_POST['meta_data']) && isset($_POST['downloadtpm'])) { //If download tpm
        foreach($_POST['meta_data'] as $check) {
          $dataline .= $check.",";
        }
        $dataline = rtrim($dataline, ",");
        $output = "$base_path/OUTPUT/tpm_".$explodedate;
	$output1 = "$base_path/OUTPUT/tpm_".$explodedate.".txt";
        $pquery = "perl ".$base_path."/SQLscripts/outputgenequery.pl -1 ".$dataline." -2 ".$output." -3 tpm";
        #echo $pquery;
	shell_exec($pquery); 
        print("<script>location.href='results.php?file=$output1&name=tpm.txt'</script>");
      }
      elseif(!empty($_POST['meta_data']) && isset($_POST['transfervalues'])) { //If transfer to sequencing information page
        foreach($_POST['meta_data'] as $check) {
          $dataline .= $check.",";
        }
        $dataline = rtrim($dataline, ",");
        $_SESSION['store'] = "yes";
        print("<script>location.href='sequence.php?libs=$dataline'</script>");
      }
      
    }
  }
?>
  </div>
<?php
  $db_conn->close();
?>
  </div> <!--in header-->		
<a class="back-to-top" style="display: inline;" href="#"><img src="images/backtotop.png" alt="Back To Top" width="45" height="45"></a>
<script src=”//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js”></script>
    <script>
      jQuery(document).ready(function() {
        var offset = 250;
        var duration = 300;
        jQuery(window).scroll(function() {
          if (jQuery(this).scrollTop() > offset) {
            jQuery(‘.back-to-top’).fadeIn(duration);
          } else {
            jQuery(‘.back-to-top’).fadeOut(duration);
          }
        });
 
        jQuery(‘.back-to-top’).click(function(event) {
          event.preventDefault();
          jQuery(‘html, body’).animate({scrollTop: 0}, duration);
          return false;
        }) 
      });
    </script>
</body>
</html>
