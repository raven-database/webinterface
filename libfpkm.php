<?php
  session_start();
  require_once('atlas_header.php'); //The header
  require_once('atlas_fns.php'); //All the routines
  d_libfpkm_header(); //Display header
  $phpscript = "libfpkm.php";
  
  if (!empty($_REQUEST['geneinfo'])) {
    $_SESSION['species'] = $_POST['species'];
  }
?>
<?php
  /* Connect to the database */
  $table = "MappingStats";
  $gquery = 'select libraryid from vw_libmetadata WHERE species = "gallus"';
  $aquery = 'select libraryid from vw_libmetadata WHERE species = "alligator_mississippiensis"';
  $mquery = 'select libraryid from vw_libmetadata WHERE species = "mus_musculus"';
?>
<?php
$liblist = null;
  if (!empty($_SESSION['species'] == "gallus")) {
    $result = $db_conn->query($gquery);
    while ($row = $result->fetch_assoc()) {
      $counter = $counter+1;
      $liblist .= $row['libraryid'].",";
    }
    $liblist = rtrim($liblist, ",");
  }
  elseif (!empty($_SESSION['species'] == "alligator_mississippiensis") ) {
    $result = $db_conn->query($aquery);
    while ($row = $result->fetch_assoc()) {
      $counter = $counter+1;
        $liblist .= $row['libraryid'].",";
      }
     $liblist = rtrim($liblist, ",");
  }
  elseif (!empty($_SESSION['species'] == "mus_musculus") ) {
    $result = $db_conn->query($mquery);
    while ($row = $result->fetch_assoc()) {
        $counter = $counter+1;
        $liblist .= $row['libraryid'].",";
      }
     $liblist = rtrim($liblist, ",");
  }
  elseif (!empty($_SESSION['species'] == "individual") ) {
    $_POST['search'] = mysqli_real_escape_string($db_conn, htmlentities($_POST['search']));
    $query = 'select libraryid from vw_libmetadata WHERE libraryid in ('.$_POST['search'].')';
    $result = $db_conn->query($query);
    while ($row = $result->fetch_assoc()) {
      $counter = $counter + 1;
        $liblist .= $row['libraryid'].",";
      }
    $liblist = rtrim($liblist, ",");
  }
?>
<div class="question">
<?php
  echo '<form id="geneall" class="top-border" action="'.$phpscript.'" method="post">';
?>
    <p class="pages"><span>Species: </span>
    <select name="species" id="species" required>
      <option value="" disabled >Select A Species or Individual libraries</option>
      <option value="individual" selected <?php if ($_POST['species']=='individual') echo 'selected="selected"'; ?> >individual</option>
      <option value="gallus" selected <?php if ($_POST['species']=='gallus') echo 'selected="selected"'; ?> >gallus</option>
			<option value="mus_musculus" <?php if ($_POST['species']=='mus_musculus') echo 'selected="selected"'; ?> >mus_musculus</option>
			<option value="alligator_mississippiensis" <?php if ($_POST['species']=='alligator_mississippiensis') echo 'selected="selected"'; ?> >alligator_mississippiensis</option>
    </select>
    <div id="individual">
      <p class="pages"><span>Input the library numbers :</span>
      <?php
        if (!empty($_POST['search'])) {
          echo '<input type="text" name="search" size="35" value="' . $_POST["search"] . '"/>';
        } else {
          echo '<input type="text" name="search" size="35" placeholder="Enter library ids separated by commas (,)" />';
        }
      ?>
      </p>
    </div> <!-- Individual div -->
    </p>
    <p class="pages"><span>Expression Values: </span>
    <select name="expvalue" id="expvalue" required>
      <option value="fpkm" selected <?php if ($_POST['expvalue']=='fpkm') echo 'selected="selected"'; ?> >fpkm</option>
			<option value="tpm" <?php if ($_POST['expvalue']=='tpm') echo 'selected="selected"'; ?> >tpm</option>
    </select>
    </p>
    
    <input type="submit" name="geneinfo" value="View Genes" onclick="doit();"/>
    <input type="submit" name="geneinfo" value="View Overlapping Genes" onclick="doit();"/><br>
</form> </div>
<hr>
<?php
  if(!empty($db_conn) && !empty($_POST['geneinfo']) && !empty($_POST['species'])) {
    //echo "yes this is $liblist<br>";
    if ($_POST['geneinfo'] == "View Genes") {
      switch ($_POST['species']) {
        case "individual":
          $thename = "GenesAll";
          $number = -1;
          break;
        case "gallus":
          $thename = "GallusAll";
          $number = shell_exec("cat /home/modupe/public_html/atlas/LIBFPKMDUMP/Gallus.no"); 
          $storedoutput = "/home/modupe/public_html/atlas/LIBFPKMDUMP/GallusAll.txt.gz";
          break;
        case "alligator_mississippiensis":
          $thename = "AlligatorAll";
          $number = shell_exec("cat /home/modupe/public_html/atlas/LIBFPKMDUMP/Alligator.no"); 
          $storedoutput = "/home/modupe/public_html/atlas/LIBFPKMDUMP/AlligatorAll.txt.gz";
          break;
        case "mus_musculus":
          $thename = "MouseAll";
          $number = shell_exec("cat /home/modupe/public_html/atlas/LIBFPKMDUMP/Mouse.no"); 
          $storedoutput = "/home/modupe/public_html/atlas/LIBFPKMDUMP/MouseAll.txt.gz";
          break;
      }
    } elseif ($_POST['geneinfo'] == "View Overlapping Genes") {
      switch ($_POST['species']) {
        case "individual";
          $thename = "GeneOverlap";
          $number = -1;
          break;
        case "gallus":
          $thename = "GallusOverlap";
          $number = shell_exec("cat /home/modupe/public_html/atlas/LIBFPKMDUMP/Gallus.no"); 
          $storedoutput = "/home/modupe/public_html/atlas/LIBFPKMDUMP/GallusOverlap.txt.gz";
          break;
        case "alligator_mississippiensis":
          $thename = "AlligatorOverlap";
          $number = shell_exec("cat /home/modupe/public_html/atlas/LIBFPKMDUMP/Alligator.no"); 
          $storedoutput = "/home/modupe/public_html/atlas/LIBFPKMDUMP/AlligatorOverlap.txt.gz";
          break;
        case "mus_musculus":
          $thename = "MouseOverlap";
          $number = shell_exec("cat /home/modupe/public_html/atlas/LIBFPKMDUMP/Mouse.no"); 
          $storedoutput = "/home/modupe/public_html/atlas/LIBFPKMDUMP/MouseOverlap.txt.gz";
          break;
      }
    }
?>
<?php
    if (!empty($liblist)){
      if ($counter == $number){
        print $storedoutput; print $thename;
        header('Location:results.php?file='.$storedoutput.'&name='.$thename.'.txt.gz');
      }
      else {
        $output = "$base_path/OUTPUT/$thename"."_".$explodedate;
        $output1 = "$base_path/OUTPUT/$thename"."_".$explodedate.".txt";
        if (preg_match("/Overlap$/",$thename)) {
          $pquery = "perl $base_path/SQLscripts/outputcommagenes.pl -1 ".$liblist." -2 ".$output." -3 ".$_POST['expvalue']."";
        }
        else {
          $pquery = "perl $base_path/SQLscripts/outputgenequery.pl -1 ".$liblist." -2 ".$output." -3 ".$_POST['expvalue']."";
        }
?>
     <br>Processing<br>
<?PHP
        // print $pquery;
        // print "\nthis is counter $counter, this is number$number";
        shell_exec($pquery);
        $filesize = explode("/", exec("du -k $output1")); 
        if ($filesize[0] > 1000 ){ //zip files larger than 1Mb
          shell_exec("gzip $output1");
          header('Location:results.php?file='.$output1.'.gz&name='.$thename.'.txt.gz');
        }
        else {
          header('Location:results.php?file='.$output1.'&name='.$thename.'.txt');
        }
      }
    }
  }
?>

<?php
  $db_conn->close();
?>
  </div> <!--in header-->		
</body>
</html>
