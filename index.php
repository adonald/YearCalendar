<?php
/************************************
*  http://www.adonald.co.uk/
*  Alex Donald 2014-01-21
************************************/

// Check that only digits have been entered for year
// Change html from input into special characters
// Return chosen year, or default to current year
if (ctype_digit($_GET["year"])) {
    $year = htmlspecialchars($_GET["year"]);
} else {
    $year = date("Y"); //current year
}

// Check that only digits have been entered for month
// Change html from input into special characters
// Return chosen month, or default to current month
if (ctype_digit($_GET["month"])) {
    $month = htmlspecialchars($_GET["month"]);
} else {
    $month = date("n"); // current month
}
// set up the month name array
$months = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
// Create select month drop down for form
function monthDropdown($selected,$month_array) {
    $dd = "\t\t<select name='month' id='month' class='month'>\n";
    for ($i = 1; $i <= 12; $i++) {
        $dd .= "\t\t\t<option value='".$i."'";
        if ($i == $selected) {
            $dd .= " selected";
        }
        /*** get the month ***/
        $dd .= ">".$month_array[$i]."</option>\n";
    }
    $dd .= "\t\t</select>\n";
    return $dd;
}

// Set up acceptable options for bank holidays
$possible_bank_holidays =  ["england-and-wales", "scotland", "northern-ireland", "none"];
// Check that $holidays is an acceptable option
// or default to england-and-wales
if (in_array($_GET["holidays"], $possible_bank_holidays)) {
    $holidays = htmlspecialchars($_GET["holidays"]);
} else {
    $holidays = "england-and-wales";
}
// Create select region drop down for form
function bankHolidaysDropdown($selected) {
    $dd = "\t\t<select name='holidays' id='holidays' class='holidays'>\n";
    $bankHolidays = ['england-and-wales' => 'England and Wales', 'scotland' => 'Scotland', 'northern-ireland' => 'Northern Ireland', 'none' => 'None'];
    foreach ($bankHolidays as $key => $val) {
        $dd .= "\t\t\t<option value='".$key."'";
        if ($key == $selected) {
            $dd .= " selected";
        }
        $dd .= ">".$val."</option>\n";
    }
    $dd .= "\t\t</select>\n";
    return $dd;
}

// Set up acceptable options for calendar type
$calendar_type =  ["year_months", "single_month"];
// Check that $type is an acceptable option
// or default to year_months
if (in_array($_GET["type"], $calendar_type)) {
    $type = htmlspecialchars($_GET["type"]);
} else {
    $type = "year_months";
}
// Create select calendar type drop down for form
function calendarTypesDropdown($selected) {
    $dd = "\t\t<select name='type' id='calendar-type' class='calendar-type'>\n";
    $calendar_types = ['year_months' => 'Yearly Planner', 'single_month' => 'Single Month'];
    foreach ($calendar_types as $key => $val) {
        $dd .= "\t\t\t<option value='".$key."'";
        if ($key == $selected) {
            $dd .= " selected";
        }
        $dd .= ">".$val."</option>\n";
    }
    $dd .= "\t\t</select>\n";
    return $dd;
}

function calendar($type, $year, $month, $holidays) {

    if ($holidays != "none") {
        // Get array of bank holidays
        $gov_uk_bank_holidays = json_decode(file_get_contents('https://www.gov.uk/bank-holidays.json'),true);
        $bank_holidays = $gov_uk_bank_holidays[$holidays]["events"];
    } else {
        $bank_holidays = false;
    }

    if ($type == "year_months") {
        // Display all months in current or chosen year
        $month = 1; // Over-ride $month, set 1st month to Jan
        $calendar = "<section class=\"year-calendar\">\n";
        while($month <= 12){
            $calendar .= month_calendar($year, $month, $bank_holidays);
            $month++;
        }
        $calendar .= "</section>\n";
        return $calendar;
    } elseif ($type == "single_month") {
        $calendar = "<section class=\"single-month-calendar\">\n";
        $calendar .= month_calendar($year, $month, $bank_holidays);
        $calendar .= "</section>\n";
        return $calendar;
    }
}

// find $date within bank holidays array
function is_date_holiday($id, $array) {
    foreach ($array as $key => $val) {
        if ($val["date"] == $id) {
            return true;
        }
    }
    return false;
}

function month_calendar($year, $month, $bank_holidays) {

    // Create array containing abbreviations of days of week.
    $daysOfWeek = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');

    // What is the first day of the month in question?
    $firstDayOfMonth = mktime(0,0,0,$month,1,$year);

    // How many days does this month contain?
    $numberDays = date('t',$firstDayOfMonth);

    // Retrieve some information about the first day of the
    // month in question, store in array.
    $dateComponents = getdate($firstDayOfMonth);

    // What is the name of the month in question?
    $monthName = $dateComponents['month'];

    // What is the index value (0-6) of the first day of the
    // month in question.
    // Subtract 1 from value to start week on Monday
    // (looping Sun back to replace Sat)
    //$dayOfWeek = ($dateComponents['wday'] % 7) - 1;
    //$dayOfWeek = ($dateComponents['wday'] - 1) % 7;
    if ($dateComponents['wday'] == 0) {
        $dayOfWeek = 6;
    } else {
        $dayOfWeek = $dateComponents['wday'] - 1;
    }

    // Create the table tag opener and day headers
    $month_calendar = "<table>\n";
    $month_calendar .= "\t<caption>$monthName $year</caption>\n";
    $month_calendar .= "\t<thead>\n";
    $month_calendar .= "\t\t<tr>\n";

    // Create the calendar headers
    foreach($daysOfWeek as $day) {
        $month_calendar .= "\t\t\t<th>$day</th>\n";
    } 

    $month_calendar .= "\t\t</tr>\n\t</thead>\n";

    // Create the rest of the calendar
    
    $month_calendar .= "\t<tbody>\n\t\t<tr>\n";

    // Initiate the day counter, starting with the 1st.
    $currentDay = 1;
    // Initiate the row counter, starting with 1
    $weekRow = 1;

    // The variable $dayOfWeek is used to
    // ensure that the calendar
    // display consists of exactly 7 columns.
    for ($i = 0; $i < $dayOfWeek; $i++) {
        $month_calendar .= "\t\t\t<td rel=\"empty\">&nbsp;</td>\n";
    }

    $month = str_pad($month, 2, "0", STR_PAD_LEFT);

    while ($currentDay <= $numberDays) {

        // Seventh column (Sunday) reached. Start a new row.
        if ($dayOfWeek == 7) {

            $dayOfWeek = 0;
            $month_calendar .= "\t\t</tr>\n\t\t<tr>\n";
            $weekRow++;

        }

        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);

        $date = "$year-$month-$currentDayRel";

        // Is day a bank holiday
        $class = !empty($bank_holidays) && is_date_holiday($date, $bank_holidays)
                ? 'class="holiday"'
                : "";

        // change class for weekend or weekday, and holiday
        $month_calendar .= "\t\t\t<td $class rel='$date'>$currentDay</td>\n";

        // Increment counters
        $currentDay++;
        $dayOfWeek++;

    }

    // Complete the row of the last week in month, if necessary
    for ($i = $dayOfWeek; $i < 7; $i++) {
        $month_calendar .= "\t\t\t<td rel=\"empty\">&nbsp;</td>\n";
    }

    $month_calendar .= "\t</tr>\n";

    $month_calendar .= "\t</tbody>\n</table>\n";

    return $month_calendar;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable Calendar &#124; www.alexdonald.co.uk</title>
    <meta name="description" content="Printable yearly and monthly calendar">
    <meta name="keywords" content="calendar, printable, print, yearly, year, monthly, month">
    <meta name="author" content="Alex Donald">
    <link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<main>
    <nav>
        <div class="noprint">
            <form action="" method="get">
                <label for="year">Year:</label>
                <input type="number" class="year" name="year" value="<?php echo $year; ?>" />
                <label for="month">Month:</label>
                <?php echo monthDropdown($month,$months); ?>
                <label for="holidays">Bank Holidays:</label>
                <?php echo bankHolidaysDropdown($holidays); ?>
                <label for="type">Calendar Type:</label>
                <?php echo calendarTypesDropdown($type); ?>
                <input type="submit" class="submit" value="submit" />
            </form>
        </div>
    </nav>

<?php
// Display all months in current or chosen year
echo calendar($type, $year, $month, $holidays);
?>

</main>
</body>
</html>
