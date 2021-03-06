<?php
/*******************************************************************************
 *
 *  filename    : FindFundRaiser.php
 *  last change : 2009-04-16
 *  website     : http://www.churchdb.org
 *  copyright   : Copyright 2009 Michael Wilt
 *
 *  ChurchInfo is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 ******************************************************************************/

//Include the function library
require "Include/Config.php";
require "Include/Functions.php";

//Set the page title
$sPageTitle = gettext("Fundraiser Listing");

//Filter Values
$dDateStart = "";
$dDateEnd = "";
$iID = "";
$sSort = "";

if (array_key_exists ("DateStart", $_GET))
	$dDateStart = FilterInput($_GET["DateStart"]);
if (array_key_exists ("DateEnd", $_GET))
	$dDateEnd = FilterInput($_GET["DateEnd"]);
if (array_key_exists ("ID", $_GET))
	$iID = FilterInput($_GET["ID"]);
if (array_key_exists ("Sort", $_GET))
	$sSort = FilterInput($_GET["Sort"]);

// Build SQL Criteria
$sCriteria = "";
if ($dDateStart || $dDateEnd) {
	if (!$dDateStart && $dDateEnd)
		$dDateStart = $dDateEnd;
	if (!$dDateEnd && $dDateStart)
		$dDateEnd = $dDateStart;
	$sCriteria .= " WHERE fr_Date BETWEEN '$dDateStart' AND '$dDateEnd' ";
}
if ($iID) {
	if ($sCriteria)
		$sCrieria .= "OR fr_ID = '$iID' ";
	else
		$sCriteria = " WHERE fr_ID = '$iID' ";
}
if (array_key_exists ("FilterClear", $_GET) && $_GET["FilterClear"]) {
	$sCriteria = "";
	$dDateStart = "";
	$dDateEnd = "";
	$iID = "";
}
require "Include/Header.php";

?>

<form method="get" action="FindFundRaiser.php" name="FindFundRaiser">
<input name="sort" type="hidden" value="<?php echo $sSort; ?>"
<table cellpadding="3" align="center">

	<tr>
		<td>
		<table cellpadding="3">
			<tr>
				<td class="LabelColumn"><?php echo gettext("Number:"); ?></td>
				<td class="TextColumn"><input type="text" name="ID" id="ID" value="<?php echo $iID; ?>"></td>
			</tr>

			<tr>
				<td class="LabelColumn"<?php addToolTip("Format: YYYY-MM-DD<br>or enter the date by clicking on the calendar icon to the right."); ?>><?php echo gettext("Date Start:"); ?></td>
				<td class="TextColumn"><input type="text" name="DateStart" maxlength="10" id="sel1" size="11" value="<?php echo $dDateStart; ?>">&nbsp;<input type="image" onclick="return showCalendar('sel1', 'y-mm-dd');" src="Images/calendar.gif"> <span class="SmallText"><?php echo gettext("[YYYY-MM-DD]"); ?></span></td>
				<td align="center">
					<input type="submit" class="icButton" value="<?php echo gettext("Apply Filters"); ?>" name="FindFundRaiserSubmit">
				</td>
			</tr>
			<tr>
				<td class="LabelColumn"<?php addToolTip("Format: YYYY-MM-DD<br>or enter the date by clicking on the calendar icon to the right."); ?>><?php echo gettext("Date End:"); ?></td>
				<td class="TextColumn"><input type="text" name="DateEnd" maxlength="10" id="sel2" size="11" value="<?php echo $dDateEnd; ?>">&nbsp;<input type="image" onclick="return showCalendar('sel2', 'y-mm-dd');" src="Images/calendar.gif"> <span class="SmallText"><?php echo gettext("[YYYY-MM-DD]"); ?></span></td>
				<td align="center">
					<input type="submit" class="icButton" value="<?php echo gettext("Clear Filters"); ?>" name="FilterClear">
				</td>
			</tr>
		</table>
		</td>
	</form>
</table>


<?php
// List Fundraisers
// Save record limit if changed
if (isset($_GET["Number"]))
{
	$_SESSION['SearchLimit'] = FilterInput($_GET["Number"],'int');
	$uSQL = "UPDATE user_usr SET usr_SearchLimit = " . $_SESSION['SearchLimit'] . " WHERE usr_per_ID = " . $_SESSION['iUserID'];
	$rsUser = RunQuery($uSQL);
}

// Select the proper sort SQL
switch($sSort)
{
	case "number":
		$sOrderSQL = "ORDER BY fr_ID DESC";
		break;
	default:
		$sOrderSQL = " ORDER BY fr_Date DESC, fr_ID DESC";
		break;
}

// Append a LIMIT clause to the SQL statement
$iPerPage = $_SESSION['SearchLimit'];
if (empty($_GET['Result_Set']))
	$Result_Set = 0;
else
	$Result_Set = FilterInput($_GET['Result_Set'],'int');
$sLimitSQL = " LIMIT $Result_Set, $iPerPage";

// Build SQL query
$sSQL = "SELECT fr_ID, fr_Date, fr_Title FROM fundraiser_fr $sCriteria $sOrderSQL $sLimitSQL";
$sSQLTotal = "SELECT COUNT(fr_ID) FROM fundraiser_fr $sCriteria";

// Execute SQL statement and get total result
$rsDep = RunQuery($sSQL);
$rsTotal = RunQuery($sSQLTotal);
list ($Total) = mysqli_fetch_row($rsTotal);

echo '<div align="center">';
echo  '<form action="FindFundRaiser.php" method="get" name="ListNumber">';
// Show previous-page link unless we're at the first page
if ($Result_Set < $Total && $Result_Set > 0)
{
	$thisLinkResult = $Result_Set - $iPerPage;
	if ($thisLinkResult < 0)
		$thisLinkResult = 0;
	echo '<a href="FindFundRaiser.php?Result_Set='.$thisLinkResult.'&Sort='.$sSort.'">'. gettext("Previous Page") . '</a>&nbsp;&nbsp;';
}

// Calculate starting and ending Page-Number Links
$Pages = ceil($Total / $iPerPage);
$startpage =  (ceil($Result_Set / $iPerPage)) - 6;
if ($startpage <= 2)
	$startpage = 1;
$endpage = (ceil($Result_Set / $iPerPage)) + 9;
if ($endpage >= ($Pages - 1))
	$endpage = $Pages;

// Show Link "1 ..." if startpage does not start at 1
if ($startpage != 1)
	echo "<a href=\"FindFundRaiser.php?Result_Set=0&Sort=$sSort&ID=$iID&DateStart=$dDateStart&DateEnd=$dDateEnd\">1</a> ... ";

// Display page links
if ($Pages > 1)
{
	for ($c = $startpage; $c <= $endpage; $c++)
	{
		$b = $c - 1;
		$thisLinkResult = $iPerPage * $b;
		if ($thisLinkResult != $Result_Set)
			echo "<a href=\"FindFundRaiser.php?Result_Set=$thisLinkResult&Sort=$sSort&ID=$iID&DateStart=$dDateStart&DateEnd=$dDateEnd\">$c</a>&nbsp;";
		else
			echo "&nbsp;&nbsp;[ " . $c . " ]&nbsp;&nbsp;";
	}
}

// Show Link "... xx" if endpage is not the maximum number of pages
if ($endpage != $Pages)
{
	$thisLinkResult = ($Pages - 1) * $iPerPage;
		echo " <a href=\"FindFundRaiser.php?Result_Set=$thisLinkResult&Sort=$sSort&ID=$iID&DateStart=$dDateStart&DateEnd=$dDateEnd\">$Pages</a>";
}

// Show next-page link unless we're at the last page
if ($Result_Set >= 0 && $Result_Set < $Total)
{
	$thisLinkResult=$Result_Set+$iPerPage;
	if ($thisLinkResult<$Total)
		echo "&nbsp;&nbsp;<a href='FindFundRaiser.php?Result_Set=$thisLinkResult&Sort=$sSort'>". gettext("Next Page") . "</a>&nbsp;&nbsp;";
}


// Display Record Limit
echo "<input type=\"hidden\" name=\"Result_Set\" value=\"" . $Result_Set . "\">";
if(isset($sSort))
	echo "<input type=\"hidden\" name=\"Sort\" value=\"" . $sSort . "\">";

$sLimit5 = "";
$sLimit10 = "";
$sLimit20 = "";
$sLimit25 = "";
$sLimit50 = "";

if ($_SESSION['SearchLimit'] == "5")
	$sLimit5 = "selected";
if ($_SESSION['SearchLimit'] == "10")
	$sLimit10 = "selected";
if ($_SESSION['SearchLimit'] == "20")
	$sLimit20 = "selected";
if ($_SESSION['SearchLimit'] == "25")
	$sLimit25 = "selected";
if ($_SESSION['SearchLimit'] == "50")
	$sLimit50 = "selected";
			
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". gettext("Display:") . "&nbsp;
	<select class=\"SmallText\" name=\"Number\">
		<option value=\"5\" $sLimit5>5</option>
		<option value=\"10\" $sLimit10>10</option>
		<option value=\"20\" $sLimit20>20</option>
		<option value=\"25\" $sLimit25>25</option>
		<option value=\"50\" $sLimit50>50</option>
	</select>&nbsp;
	<input type=\"submit\" class=\"icTinyButton\" value=\"". gettext("Go") ."\">
	</form></div><br>";

// Column Headings
echo "<table cellpadding='4' align='center' cellspacing='0' width='100%'>\n
	<tr class='TableHeader'>\n
	<td width='25'>".gettext("Edit") . "</td>\n
	<td><a href='FindFundRaiser.php?Sort=number&ID=$iID&DateStart=$dDateStart&DateEnd=$dDateEnd'>".gettext("Number")."</a></td>\n
	<td><a href='FindFundRaiser.php?Sort=date'&ID=$iID&DateStart=$dDateStart&DateEnd=$dDateEnd>".gettext("Date")."</a></td>\n
	<td>".gettext("Title")."</td>\n
	</tr>";

// Display Deposits
while (list ($fr_ID, $fr_Date, $fr_Title) = mysqli_fetch_row($rsDep))
{
	echo "<tr><td><a href='FundRaiserEditor.php?FundRaiserID=$fr_ID'>" . gettext("Edit") . "</td>";
	echo "<td>$fr_ID</td>";
	echo "<td>$fr_Date</td>";
	// Get deposit total
	echo "<td>$fr_Title</td>";
}
echo "</table>";

require "Include/Footer.php";
?>
