<?php
/************************************
*  Shamelessly copied from
*  http://css-tricks.com/snippets/php/build-a-calendar-table/
*  Then modified for my
*  own nefarious purposes
*  http://www.adonald.co.uk/calendar
*  Alex Donald 2013-09-16
************************************/

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
        $calendar = "<div class=\"block-group year-calendar\">\n";
        while($month <= 12){
            $calendar .= month_calendar($year, $month, $bank_holidays);
            $month++;
        }
        $calendar .= "</div>\n";
        return $calendar;
    } elseif ($type == "single_month") {
        $calendar = "<div class=\"block-group single-month-calendar\">\n";
        $calendar .= month_calendar($year, $month, $bank_holidays);
        $calendar .= "</div>\n";
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
    return null;
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
    if ($dateComponents['wday'] == 0) {
        $dayOfWeek = 6;
    } else {
        $dayOfWeek = $dateComponents['wday'] - 1;
    }

    // Create the table tag opener and day headers
    $month_calendar = "<table class='block month-calendar'>\n";
    $month_calendar .= "\t<caption class='month'>$monthName $year</caption>\n";
    $month_calendar .= "\t<tr>\n";

    // Create the calendar headers
    foreach($daysOfWeek as $day) {
        $month_calendar .= "\t\t<th class='table-header'>$day</th>\n";
    } 

    // Create the rest of the calendar

    // Initiate the day counter, starting with the 1st.
    $currentDay = 1;
    // Initiate the row counter, starting with 1
    $weekRow = 1;

    $month_calendar .= "\t</tr>\n\t<tr>\n";

    // The variable $dayOfWeek is used to
    // ensure that the calendar
    // display consists of exactly 7 columns.
    if ($dayOfWeek > 0) { 
        $month_calendar .= "\t\t<td colspan='$dayOfWeek'>&nbsp;</td>\n"; 
    }

    $month = str_pad($month, 2, "0", STR_PAD_LEFT);

    while ($currentDay <= $numberDays) {

        // Seventh column (Sunday) reached. Start a new row.
        if ($dayOfWeek == 7) {

            $dayOfWeek = 0;
            $month_calendar .= "\t</tr>\n\t<tr>\n";
            $weekRow++;

        }

        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);

        $date = "$year-$month-$currentDayRel";

        // Is day a weekday or a weekend
        if ($dayOfWeek >= 5) {
            $class = "weekend";
        } else {
            $class = "weekday";
        }

        // Is day a bank holiday
        if ($bank_holidays != false) {
            if (is_date_holiday($date, $bank_holidays)) {
                $class .= " holiday";
            }
        }

        // change class for weekend or weekday, and holiday
        $month_calendar .= "\t\t<td class='$class' rel='$date'>$currentDay</td>\n";

        // Increment counters
        $currentDay++;
        $dayOfWeek++;

    }

    // Complete the row of the last week in month, if necessary
    if ($dayOfWeek != 7) { 
        $remainingDays = 7 - $dayOfWeek;
        $month_calendar .= "\t\t<td colspan='$remainingDays'>&nbsp;</td>\n"; 
    }

    $month_calendar .= "\t</tr>\n";

    // Add blank row to bottom of table if necessary to ensure all
    // months are the same height
    if ($weekRow <= 5) {
        $month_calendar .= "\t<tr>\n\t\t<td colspan='7'>&nbsp;</td>\n\t</tr>\n";
    }

    $month_calendar .= "</table>\n";

    return $month_calendar;
}

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
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $year; ?> Calendar &mdash; www.adonald.co.uk</title>
    <link rel="stylesheet" href="/pocketgrid/pocketgrid.min.css" />
    <link rel="stylesheet" href="calendar.css" />
    <link rel="stylesheet" href="calendar.print.css" media="print" />
</head>
<body>

<?php
// Display all months in current or chosen year
echo calendar($type, $year, $month, $holidays);
?>

</body>
</html>
