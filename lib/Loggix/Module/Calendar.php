<?php 
/**
 * Loggix_Module_Calendar - Calendar Module for Loggix
 *
 * PHP version 5
 *
 * @package   Loggix_Module
 * @copyright Copyright (C) Loggix Project
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.31
 * @version   11.5.28
 */


/**
 * Include Module class
 */
require_once $pathToIndex . '/lib/Loggix/Module.php';


/**
 * @package   Loggix_Module
 */
class Loggix_Module_Calendar extends Loggix_Module
{
    const   CALENDAR_THEME_PATH = '/modules/calendar/theme/';

    private $_allDateArray  = null;
    private $_allMonthArray = null;
    private $_allYearArray  = null;
    
    /**
     * Initialize Month Array
     */
    public function initMonthArray($date, $mode)
    {
        if ($mode == 'access') {
            $targetTable   = ACCESSLOG_TABLE;
            $targetField1  = 'date';
            $operator      = '';
            $additionalSql = "";
        } elseif ($mode == 'comments') {
            $targetTable   = COMMENT_TABLE;
            $targetField1  = 'date';
            $operator      = ' AND ';
            $additionalSql = "trash = '0'";
        } elseif ($mode == 'trackbacks') {
            $targetTable   = TRACKBACK_TABLE;
            $targetField1  = 'date';
            $operator      = ' AND ';
            $additionalSql = "trash = '0'";
        } elseif ($mode == 'downloads') {
            $targetTable   = DOWNLOADS_META_TABLE;
            $targetField1  = 'file_date';
            $operator      = ' AND ';
            $additionalSql = "draft = '0'";
        } else {
            $targetTable   = LOG_TABLE;
            $targetField1  = 'date';
            $operator      = ' AND ';
            $additionalSql = "draft = '0'";
        }

        /* --- DD Array --- */
        $sql = 'SELECT '
             .     'SUBSTR(' . $targetField1 . ', 9, 2) as day '
             . 'FROM '
             .     $targetTable . ' '
             . 'WHERE '
             .     '(' . $targetField1 . " LIKE '" . $date . "%')"
             .     $operator . $additionalSql . ' '
             . 'GROUP BY '
             .     'day';

        $result = array();

        if ($res = $this->db->query($sql)) {
            while ($day = $res->fetch()) {
                $result[$day[0]] = true;
            }
            $this->_allDateArray = $result;
        }

        /* --- YYYY-MM Array --- */
        $sql2 = 'SELECT '
             .     'SUBSTR(' . $targetField1 . ', 1, 7) as month '
             . 'FROM '
             .     $targetTable . ' '
             . 'WHERE '
             .     $additionalSql . ' '
             . 'GROUP BY '
             .     'month '
             . 'ORDER BY '
             .     'month ASC';

        $result2 = array();

        if ($res2 = $this->db->query($sql2)) {
            while ($month = $res2->fetch()) {
               $result2[$month[0]] = substr($month[0], 0, -3);
            }
            $this->_allMonthArray = $result2;
        }

        /* --- YYYY Array --- */
        $sql3 = 'SELECT '
             .     'SUBSTR(' . $targetField1 . ', 1, 4) as year '
             . 'FROM '
             .     $targetTable . ' '
             . 'WHERE '
             .     $additionalSql . ' '
             . 'GROUP BY '
             .     'year '
             . 'ORDER BY '
             .     'year ASC';

        $result3 = array();

        if ($res3 = $this->db->query($sql3)) {
            while ($year = $res3->fetch()) {
                $result3[] = $year[0];
            }
            $this->_allYearArray = $result3;
        }


    }
    
    /**
     * Check if the date has log
     *
     * @param  $date
     * @param  $mode
     * @return boolean
     */
    public function hasLog($date, $mode)
    {
        if (is_array($this->_allDateArray) && isset($this->_allDateArray[$date])) {
            return true;
        }
        return false;
    }


    /**
     * Set Mode
     *
     * @return string 'search' | 'admin' | 'access' | 'downloads'
     */
    public function setMode()
    {
        if (strpos($this->getRequestURI(), 'access')) {
            return 'access';
        } elseif (strpos($this->getRequestURI(), 'downloads')) {
            return 'downloads';
        } elseif (strpos($this->getRequestURI(), 'comment')) {
            return 'comments';
        } elseif (strpos($this->getRequestURI(), 'trackback')) {
            return 'trackbacks';
        } else {
            return 'log';
        }
    }

    /**
     * Create calendar
     *
     * @param  $mode  mode:== 'search' | 'admin' | 'access' | 'downloads'
     * @return string 
     */
    public function createCalendar()
    {
        global $pathToIndex, $item, $lang;
        
        // Set calendar mode
        $mode = $this->setMode();
        
        // Get calendar module language
        $this->getModuleLanguage('calendar');
        
        // Enable simple queries
        if ((empty($_GET['k']))  && 
            (empty($_GET['p']))  && 
            (empty($_GET['pn'])) && 
            (empty($_GET['c']))) {
            $_GET['k']  = '';
            $_GET['p']  = '0';
            $_GET['pn'] = '1';
            $_GET['c']  = '0';
        }

        // If the date query is sent, regard it as a key value.
        // if not, use the current date as a key.
        if (!empty($_GET['d'])) {   
            $dateStr = $_GET['d'];
            if (preg_match('/^[0-9]{4}-[0-9]{2}/', $dateStr)) {
                $yyyy  = substr($dateStr, 0, 4);
                $mm    = substr($dateStr, 5, 2);
                $dayKey = getdate(mktime(0, 0, 0, $mm, 1, $yyyy));
            } else {
                $dayKey = getdate();
            }
        } else {
            $dayKey = getdate();
        }

        // Variable $dayKey is an array, so pull out the list data
        // and split it into the separated variables.
        $mon  = $dayKey['mon'];
        $mday = $dayKey['mday'];
        $year = $dayKey['year'];

        // init log list
        $targetMonth = sprintf("%4d-%02d", $year, $mon);
        $this->initMonthArray($targetMonth, $mode);
        
        // For Navigation
        $monthFormat = (self::$config['language'] == 'japanese') ? 'n月' : 'M';
        $thisMonth     = date('Y-m',        mktime(0, 0, 0, $mon,     1, $year));
        $prevMonth     = date('Y-m',        mktime(0, 0, 0, $mon,     0, $year));
        $nextMonth     = date('Y-m',        mktime(0, 0, 0, $mon + 1, 1, $year));
        $prevMonthLink = date($monthFormat, mktime(0, 0, 0, $mon,     0, $year));
        $nextMonthLink = date($monthFormat, mktime(0, 0, 0, $mon + 1, 1, $year));

        // Title date format
        $formatYearAndMonth = (self::$config['language'] == 'japanese') ? 'Y年 n月' : 'F Y';
        $yearAndMonth = date($formatYearAndMonth, 
                               strtotime($mday . ' ' . $dayKey['month'] . ' ' . $year));

        // Days of the week
        $dayOfTheWeek = (self::$config['language'] == 'japanese')
                         ? array('日', '月', '火', '水', '木', '金', '土')
                         : array('S', 'M', 'T', 'W', 'T', 'F', 'S');

        // Initialize the Calendar body
        $calendarBody = '';

        // Days of the previous month
        $first_day = getdate(mktime(0, 0, 0, $mon, 1, $year));
        $weekDay = $first_day['wday'];
        for ($i = 0; $i < $weekDay; $i++) {
            $calendarBody .= '<td class="day-of-prev-month">*</td>'."\n"; 
        }

        // Change directory
        if (($mode == 'file') || ($mode == 'forum')) {
            $calDir = '.';
            $targetFile = 'index';
            $caption  = 'Forum';
        } elseif ($mode == 'admin') {
            $calDir = '../..';
            $targetFile = 'index';
            $caption  = $lang['calendar']['log_calendar'];
        } elseif ($mode == 'access') {
            $calDir = '../../modules/access';
            $targetFile = 'index';
            $caption  = $lang['calendar']['access_calendar'];
        } elseif ($mode == 'comments') {
            $calDir = '../../modules/comment';
            $targetFile = 'index';
            $caption  = $lang['calendar']['comments_calendar'];
        } elseif ($mode == 'trackbacks') {
            $calDir = '../../modules/trackback';
            $targetFile = 'index';
            $caption  = $lang['calendar']['trackbacks_calendar'];
        } elseif ($mode == 'downloads') {
            $calDir = (preg_match('/\/downloads\/admin\//', $this->getRequestUri())) ? '../../../modules/downloads' : '../../modules/downloads';
            $targetFile = 'index';
            $caption  = $lang['calendar']['downloads_calendar'];
        } else {
            $calDir = $pathToIndex;
            $targetFile = 'index';
            $caption  = $lang['calendar']['log_calendar'];
        }

        // Start making calendar
        $day = 1;
        while (checkdate($mon, $day, $year)) {
    	    $day2digitsWithLeadingZeros = sprintf('%02d', $day);
    	    if ($this->hasLog($day2digitsWithLeadingZeros, $mode)){
    	        $targetDate = sprintf("%4d-%02d-%02d", $year, $mon, $day);
                $uri = $calDir . '/'.$targetFile.'.php?d=' . $targetDate . '&amp;ex=1';
    	        $dateStr = '<td class="log-exists"><a href="'.$uri.'">'.$day.'</a></td>';
            } else {
                $dateStr = '<td>'.$day."</td>\n";
            }
            switch ($weekDay) {
            case '0': // When Sundays(0th day), add start tags ("<tr>") to start table rows.
                if ($day == 1) {
                    $calendarBody .= $dateStr;
                } else {
                    $calendarBody .= "<tr>\n" . $dateStr;
                }
                break;
            case '6': // When Saturdays(6th day), add "</tr>" elements to break table rows.
                $calendarBody .= $dateStr . "</tr>\n";
                break;
            default:
                $calendarBody .= $dateStr;
                break;
            }
            $day++;
            $weekDay++;
            $weekDay = $weekDay % 7;

        }

        // Days of the next month
        if ($weekDay > 0) {
            while ($weekDay < 7) {
                $calendarBody .= '<td class="day-of-next-month">*</td>'."\n";
                $weekDay++;
            }
            $calendarBody .= "</tr>\n";
        } else {
            $calendarBody .= '';
        }
        
        $item['calendar']['month_array']     = $this->_allMonthArray;
        $item['calendar']['year_array']      = $this->_allYearArray;
        $item['calendar']['caption']         = $caption;
        $item['calendar']['dir']             = $calDir;
        $item['calendar']['target']          = $targetFile;
        $item['calendar']['this_month']      = $thisMonth;
        $item['calendar']['year_and_month']  = $yearAndMonth;
        $item['calendar']['day_of_the_week'] = $dayOfTheWeek;
        $item['calendar']['prev_month']      = $prevMonth;
        $item['calendar']['prev_month_link'] = $prevMonthLink;
        $item['calendar']['next_month']      = $nextMonth;
        $item['calendar']['next_month_link'] = $nextMonthLink;
        $item['calendar']['body']            = $calendarBody;
        
        $calendarView = new Loggix_View($pathToIndex . self::CALENDAR_THEME_PATH . 'calendar.html');
        $calendarView->assign('item', $item);
        return $calendarView->render();
    }
}

// Instanciate Calendar

$aCal = new Loggix_Module_Calendar;
$module['LM']['calendar'] = $aCal->createCalendar();


