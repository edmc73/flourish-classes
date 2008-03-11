<?php
/**
 * Represents a date and time
 * 
 * @copyright  Copyright (c) 2007-2008 William Bond
 * @author     William Bond [wb] <will@flourishlib.com>
 * @license    http://flourishlib.com/license
 * 
 * @link  http://flourishlib.com/fTimestamp
 * 
 * @uses  fCore
 * @uses  fEnvironmentException
 * @uses  fValidationException
 * 
 * @version  1.0.0 
 * @changes  1.0.0    The initial implementation [wb, 2008-02-12]
 */
class fTimestamp
{	
	/**
	 * Pre-defined formatting styles
	 * 
	 * @var array 
	 */
	static private $formats = array();
	
	/**
	 * The date/time
	 * 
	 * @var integer 
	 */
	private $timestamp; 
	
	/**
	 * The timezone for this date/time
	 * 
	 * @var string 
	 */
	private $timezone;  
	
	
	/**
	 * Creates an fTimestamp object from fDate, fTime objects and optionally a timezone
	 * 
	 * @throws fValidationException
	 * 
	 * @param  fDate $date       The date to combine
	 * @param  fTime $time       The time to combine
	 * @param  string $timezone  The timezone for the date/time. This causes the date/time to be interpretted as being in the specified timezone. . If not specified, will default to timezone set by {@link fTimestamp::setDefaultTimezone()}.
	 * @return fTimestamp
	 */
	static public function combine(fDate $date, fTime $time, $timezone=NULL)
	{
		return new fTimestamp($date . ' ' . $time, $timezone); 
	}
	
	
	/**
	 * Creates a reusable format for formatting fDate/fTime/fTimestamp
	 * 
	 * @param  string $name               The name of the format
	 * @param  string $formatting_string  The format string compatible with the {@link http://php.net/date date()} function
	 * @return void
	 */
	static public function createFormat($name, $formatting_string)
	{
		self::$formats[$name] = $formatting_string;
	}
	
	
	/**
	 * Takes a format name set via {@link fTimestamp::createFormat()} and returns the {@link http://php.net/date date()} function formatting string
	 * 
	 * @param  string $format  The format to translate
	 * @return string  The formatting string. If no matching format was found, this will be the same as the $format parameter.
	 */
	static public function translateFormat($format)
	{
		if (isset(self::$formats[$format])) {
			$format = self::$formats[$format];	
		}
		return $format;
	}
	
	
	/**
	 * Provides a consistent interface to setting the default timezone. Wraps the {@link http://php.net/date_default_timezone_set date_default_timezone_set()} function.
	 * 
	 * @param  string $timezone  The default timezone to use for all date/time calculations
	 * @return void
	 */
	static public function setDefaultTimezone($timezone)
	{
		self::checkPHPVersion();
		
		$result = date_default_timezone_set($timezone);
		if (!$result) {
			fCore::toss('fProgrammerException', 'The timezone specified, ' . $timezone . ', does not appear to be a valid timezone');	
		}
	}
	
	
	/**
	 * Provides a consistent interface to getting the default timezone. Wraps the {@link http://php.net/date_default_timezone_get date_default_timezone_get()} function.
	 * 
	 * @return string  The default timezone used for all date/time calculations
	 */
	static public function getDefaultTimezone()
	{
		self::checkPHPVersion();
		
		return date_default_timezone_get();
	}
	
	
	/**
	 * Checks to make sure the current version of PHP is high enough to support timezone features
	 * 
	 * @return void
	 */
	static private function checkPHPVersion()
	{
		if (version_compare(fCore::getPHPVersion(), '5.1.0') == -1) {
			fCore::toss('fEnvironmentException', 'The fTimestamp class takes advantage of the timezone features in PHP 5.1.0 and newer. Unfortunately it appears you are running an older version of PHP.');	
		}	
	}
	
	
	/**
	 * Creates the date/time to represent
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $datetime   The date/time to represent
	 * @param  string $timezone   The timezone for the date/time. This causes the date/time to be interpretted as being in the specified timezone. If not specified, will default to timezone set by {@link fTimestamp::setDefaultTimezone()}.
	 * @return fTimestamp
	 */
	public function __construct($datetime, $timezone=NULL)
	{
		self::checkPHPVersion();
		
		$default_tz = date_default_timezone_get();
		
		if ($timezone) {
			if (!$this->isValidTimezone($timezone)) {
				fCore::toss('fValidationException', 'The timezone specified, ' . $timezone . ', is not a valid timezone');	
			}
			
		} else {
			$timezone = $default_tz;	
		}
		
		$this->timezone = $timezone;
		
		$timestamp = strtotime($datetime . ' ' . $timezone);
		if ($timestamp === FALSE || $timestamp === -1) {
			fCore::toss('fValidationException', 'The date/time specified, ' . $datetime . ', does not appear to be a valid date/time'); 		
		}
		
		$this->timestamp = $timestamp; 
	}
	
	
	/**
	 * Returns this date/time in the UTC timezone
	 * 
	 * @return string  The 'Y-m-d H:i:s' format of this date/time in the UTC timezone
	 */
	public function __toString()
	{
		return $this->format('Y-m-d H:i:s', 'UTC'); 
	}
	
	
	/**
	 * Changes the date to the date specified. Any parameters that are NULL are ignored.
	 * 
	 * @throws fValidationException
	 * 
	 * @param  integer $year   The year to change to
	 * @param  integer $month  The month to change to
	 * @param  integer $day    The day of the month to change to
	 * @return void
	 */
	public function setDate($year, $month, $day)
	{
		$year  = ($year === NULL)  ? date('Y', $this->timestamp) : $year;
		$month = ($month === NULL) ? date('m', $this->timestamp) : $month;
		$day   = ($day === NULL)   ? date('d', $this->timestamp) : $day;
		
		if (!is_numeric($year) || $year < 1901 || $year > 2038) {
			fCore::toss('fValidationException', 'The year specified, ' . $year . ', does not appear to be a valid year'); 				
		}
		if (!is_numeric($month) || $month < 1 || $month > 12) {
			fCore::toss('fValidationException', 'The month specified, ' . $month . ', does not appear to be a valid month'); 				
		}
		if (!is_numeric($day) || $day < 1 || $day > 31) {
			fCore::toss('fValidationException', 'The day specified, ' . $day . ', does not appear to be a valid day'); 				
		}
		
		settype($month, 'integer');
		settype($day,   'integer');
		
		if ($month < 10) { $month = '0' . $month; }
		if ($day < 10)   { $day   = '0' . $day; }
		
		$timestamp = $this->covertToTimestampWithTimezone($year . '-' . $month . '-' . $day . date(' H:i:s', $this->timestamp));
		
		if ($timestamp === FALSE || $timestamp === -1) {
			fCore::toss('fValidationException', 'The date specified, ' . $year . '-' . $month . '-' . $day . ', does not appear to be a valid date'); 		
		}
		
		$this->timestamp = $timestamp;
	}
	
	
	/**
	 * Changes the date to the ISO date (year, week, day of week) specified. Any parameters that are NULL are ignored.
	 * 
	 * @throws fValidationException
	 * 
	 * @param  integer $year         The year to change to
	 * @param  integer $week         The week to change to
	 * @param  integer $day_of_week  The day of the week to change to
	 * @return void
	 */
	public function setISODate($year, $week, $day_of_week)
	{
		$year        = ($year === NULL)        ? date('Y', $this->timestamp) : $year;
		$week        = ($week === NULL)        ? date('W', $this->timestamp) : $week;
		$day_of_week = ($day_of_week === NULL) ? date('N', $this->timestamp) : $day_of_week;
		
		if (!is_numeric($year) || $year < 1901 || $year > 2038) {
			fCore::toss('fValidationException', 'The year specified, ' . $year . ', does not appear to be a valid year'); 				
		}
		if (!is_numeric($week) || $week < 1 || $week > 53) {
			fCore::toss('fValidationException', 'The week specified, ' . $week . ', does not appear to be a valid week'); 				
		}
		if (!is_numeric($day_of_week) || $day_of_week < 1 || $day_of_week > 7) {
			fCore::toss('fValidationException', 'The day of week specified, ' . $day_of_week . ', does not appear to be a valid day of the week'); 				
		}
		
		settype($week, 'integer');
		
		if ($week < 10) { $week = '0' . $week; }
		
		$timestamp = $this->covertToTimestampWithTimezone($year . '-01-01 ' . date('H:i:s', $this->timestamp) . ' +' . ($week-1) . ' weeks +' . ($day_of_week-1) . ' days');
		
		if ($timestamp === FALSE || $timestamp === -1) {
			fCore::toss('fValidationException', 'The ISO date specified, ' . $year . '-W' . $week . '-' . $day_of_week . ', does not appear to be a valid ISO date'); 		
		}
		
		$this->timestamp = $timestamp;  
	}
	
	
	/**
	 * Changes the time to the time specified. Any parameters that are NULL are ignored.
	 * 
	 * @throws fValidationException
	 * 
	 * @param  integer $hour    The hour to change to
	 * @param  integer $minute  The minute to change to
	 * @param  integer $second  The second to change to
	 * @return void
	 */
	public function setTime($hour, $minute, $second)
	{
		$hour   = ($hour === NULL)   ? date('H', $this->timestamp) : $hour;
		$minute = ($minute === NULL) ? date('i', $this->timestamp) : $minute;
		$second = ($second === NULL) ? date('s', $this->timestamp) : $second;
		
		if (!is_numeric($hour) || $hour < 0 || $hour > 23) {
			fCore::toss('fValidationException', 'The hour specified, ' . $hour . ', does not appear to be a valid hour'); 				
		}
		if (!is_numeric($minute) || $minute < 0 || $minute > 59) {
			fCore::toss('fValidationException', 'The minute specified, ' . $minute . ', does not appear to be a valid minute'); 				
		}
		if (!is_numeric($second) || $second < 0 || $second > 59) {
			fCore::toss('fValidationException', 'The second specified, ' . $second . ', does not appear to be a valid second'); 				
		}
		
		settype($minute, 'integer');
		settype($second, 'integer');
		
		if ($minute < 10) { $minute = '0' . $minute; }
		if ($second < 10) { $second = '0' . $second; }
		
		$timestamp = $this->covertToTimestampWithTimezone(date('Y-m-d ', $this->timestamp) . $hour . ':' . $minute . ':' . $second);
		
		if ($timestamp === FALSE || $timestamp === -1) {
			fCore::toss('fValidationException', 'The time specified, ' . $time . ', does not appear to be a valid time'); 		
		}
		
		$this->timestamp = $timestamp;
	}
	
	
	/**
	 * Changes the timezone for this date/time
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $timezone  The timezone for this date/time
	 * @return void
	 */
	public function setTimezone($timezone)
	{
		if (!$this->isValidTimezone($timezone)) {
			fCore::toss('fValidationException', 'The timezone specified, ' . $timezone . ', is not a valid timezone');	
		}	
		$this->timezone = $timezone;
	}
	
	
	/**
	 * Returns the timezone for this date/time
	 * 
	 * @return string  The timezone for thie date/time
	 */
	public function getTimezone()
	{
		return $this->timezone;
	}
	
	
	/**
	 * Changes the time by the adjustment specified
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $adjustment  The adjustment to make
	 * @return void
	 */
	public function adjust($adjustment)
	{
		if ($this->isValidTimezone($adjustment)) {
			$this->setTimezone($adjustment);	
		} else {
			$this->timestamp = $this->makeAdustment($adjustment, $this->timestamp);
		}
	}
	
	
	/**
	 * Formats the date/time, with an optional adjustment of a relative date/time or a timezone
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $format      The {@link http://php.net/date date()} function compatible formatting string, or a format name from {@link fTimestamp::createFormat()}
	 * @param  string $adjustment  A temporary adjustment to make, can be a relative date/time amount or a timezone
	 * @return string  The formatted (and possibly adjusted) date/time
	 */
	public function format($format, $adjustment=NULL)
	{
		$format = self::translateFormat($format);
		
		$timestamp = $this->timestamp;
		
		// Handle an adjustment that is a timezone
		if ($adjustment && $this->isValidTimezone($adjustment)) {
			$default_tz = date_default_timezone_get();	
			date_default_timezone_set($adjustment);
			
		} else {
			$default_tz = date_default_timezone_get();		
			date_default_timezone_set($this->timezone);	
		}
		
		// Handle an adjustment that is a relative date/time
		if ($adjustment && !$this->isValidTimezone($adjustment)) {
			$timestamp = $this->makeAdjustment($adjustment, $timestamp);
		}
		
		$formatted = date($format, $timestamp);
		
		date_default_timezone_set($default_tz);
		
		return $formatted;
	}
	
	
	/**
	 * Makes an adjustment, returning the adjusted time
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $adjustment  The adjustment to make
	 * @param  integer $timestamp  The time to adjust
	 * @return integer  The adjusted timestamp
	 */
	private function makeAdjustment($adjustment, $timestamp)
	{
		$timestamp = strtotime($adjustment, $timestamp);
		
		if ($timestamp === FALSE || $timestamp === -1) {
			fCore::toss('fValidationException', 'The adjustment specified, ' . $adjustment . ', does not appear to be a valid relative date/time measurement'); 		
		}  
		
		return $timestamp;
	}
	
	
	/**
	 * Takes a date/time to pass to strtotime and interprets it using the current timestamp's timezone
	 * 
	 * @param  string $datetime  The datetime to interpret
	 * @return integer  The timestamp
	 */
	private function covertToTimestampWithTimezone($datetime)
	{
		$default_tz = date_default_timezone_get();
		date_default_timezone_set($this->timezone);
		$timestamp = strtotime($datetime);
		date_default_timezone_set($default_tz);
		return $timestamp;
	}
	
	
	/**
	 * Checks to see if a timezone is valid
	 * 
	 * @param  string $timezone  The timezone to check
	 * @param  integer $timestamp  The time to adjust
	 * @return integer  The adjusted timestamp
	 */
	private function isValidTimezone($timezone)
	{
		$default_tz = date_default_timezone_get();
		$valid_tz = @date_default_timezone_set($timezone);
		date_default_timezone_set($default_tz);
		return $valid_tz;
	}
}


/**
 * Copyright (c) 2008 William Bond <will@flourishlib.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */ 
?>