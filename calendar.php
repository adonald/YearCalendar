<?php
/************************************
*  Shamelessly copied from
*  http://css-tricks.com/snippets/php/build-a-calendar-table/
*  Then modified for my
*  own nefarious purposes
************************************/

function build_calendar($month,$year) {

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
    $calendar = "<table class='block calendar'>\n";
    $calendar .= "\t<caption class='month'>$monthName $year</caption>\n";
    $calendar .= "\t<tr>\n";

    // Create the calendar headers
    foreach($daysOfWeek as $day) {
        $calendar .= "\t\t<th class='table-header'>$day</th>\n";
    } 

    // Create the rest of the calendar

    // Initiate the day counter, starting with the 1st.
    $currentDay = 1;
    // Initiate the row counter, starting with 1
    $weekRow = 1;

    $calendar .= "\t</tr>\n\t<tr>\n";

    // The variable $dayOfWeek is used to
    // ensure that the calendar
    // display consists of exactly 7 columns.
    if ($dayOfWeek > 0) { 
        $calendar .= "\t\t<td colspan='$dayOfWeek'>&nbsp;</td>\n"; 
    }

    $month = str_pad($month, 2, "0", STR_PAD_LEFT);

    while ($currentDay <= $numberDays) {

        // Seventh column (Sunday) reached. Start a new row.
        if ($dayOfWeek == 7) {

            $dayOfWeek = 0;
            $calendar .= "\t</tr>\n\t<tr>\n";
            $weekRow++;

        }

        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);

        $date = "$year-$month-$currentDayRel";

        // change class for weekend or weekday
        if ($dayOfWeek >= 5) {
            $calendar .= "\t\t<td class='weekend' rel='$date'>$currentDay</td>\n";
        } else {
            $calendar .= "\t\t<td class='weekday' rel='$date'>$currentDay</td>\n";
        }

        // Increment counters
        $currentDay++;
        $dayOfWeek++;

    }

    // Complete the row of the last week in month, if necessary
    if ($dayOfWeek != 7) { 
        $remainingDays = 7 - $dayOfWeek;
        $calendar .= "\t\t<td colspan='$remainingDays'>&nbsp;</td>\n"; 
    }

    $calendar .= "\t</tr>\n";

    // Add blank row to bottom of table if necessary to ensure all
    // months are the same height
    if ($weekRow <= 5) {
        $calendar .= "\t<tr>\n\t\t<td colspan='7'>&nbsp;</td>\n\t</tr>\n";
    }

    $calendar .= "</table>\n";

    return $calendar;
}
?>