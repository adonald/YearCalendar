<?php
// Include calendar function file
include 'calendar.php';

// Check that only digits have been entered into the form
// Change html from input into special characters
// Return chosen year, or default to current year
if (ctype_digit($_POST["year"])) {
    $year = htmlspecialchars($_POST["year"]);
} else {
    $year = date("Y"); //current year
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $year; ?> Calendar &mdash; www.adonald.co.uk</title>
    <link rel="stylesheet" href="/pockegrid/pocketgrid.min.css" />
    <link rel="stylesheet" href="calendar.css" />
    <link rel="stylesheet" href="calendar.print.css" media="print" />
</head>
<body>
<div class="block-group noprint site-header">
    <div class="block site-name">
        <h1><a href="/" title="www.adonald.co.uk">www.adonald.co.uk</a></h1>
    </div>
    <div class="block page-title">
        <h2>Printable yearly calendar</h2>
    </div>
</div>
<div class="block-group noprint">
    <form action="" method="post" class="block select-year">
        <label for="year">Year:</label>
        <input type="number" id="year" class="year" name="year" value="<?php echo $year; ?>" />
        <button type="submit" name="change">Change</button>
    </form>
</div>
<div class="block-group">
<?php
// Display all months in current or chosen year
$month = 1; // Set 1st month to Jan
while($month <= 12){
    echo build_calendar($month,$year);
    $month++;
}
?>
</div>
</body>
</html>
