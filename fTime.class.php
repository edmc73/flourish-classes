<?php
/**
 * Represents a time of day
 * 
 * @copyright  Copyright (c) 2007-2008 William Bond
 * @author     William Bond [wb] <will@flourishlib.com>
 * @license    http://flourishlib.com/license
 * 
 * @link  http://flourishlib.com/fTime
 * 
 * @uses  fCore
 * @uses  fProgrammerException
 * @uses  fTimestamp
 * @uses  fValidationException
 * 
 * @version  1.0.0 
 * @changes  1.0.0    The initial implementation [wb, 2008-02-12]
 */
class fTime
{	
	/**
	 * A timestamp of the time
	 * 
	 * @var integer 
	 */
	private $time;   
	
	
	/**
	 * Creates the time to represent, no timezone is allowed since times don't have timezones
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $time      The time to represent
	 * @return fTime
	 */
	public function __construct($time)
	{
		$parsed = strtotime($time);
		if ($parsed === FALSE || $parsed === -1) {
			fCore::toss('fValidationException', 'The time specified, ' . $time . ', does not appear to be a valid time'); 		
		}
		$this->set($parsed); 
	}
	
	
	/**
	 * Returns this time in 'H:i:s' format
	 * 
	 * @return string  The 'H:i:s' format of this time
	 */
	public function __toString()
	{
		return $this->format('H:i:s'); 
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
		$hour   = ($hour === NULL)   ? date('H', $this->time) : $hour;
		$minute = ($minute === NULL) ? date('i', $this->time) : $minute;
		$second = ($second === NULL) ? date('s', $this->time) : $second;
		
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
		
		$parsed = strtotime($hour . ':' . $minute . ':' . $second);
		if ($parsed === FALSE || $parsed === -1) {
			fCore::toss('fValidationException', 'The time specified, ' . $time . ', does not appear to be a valid time'); 		
		}
		$this->set($parsed); 
	}
	
	
	/**
	 * Changes the time by the adjustment specified, only asjustments of 'hours', 'minutes', 'seconds' are allowed
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $adjustment  The adjustment to make
	 * @return void
	 */
	public function adjust($adjustment)
	{
		$time = $this->makeAdustment($adjustment, $this->time);
		$this->set($time);
	}
	
	
	/**
	 * Formats the time, with an optional adjustment
	 * 
	 * @throws fValidationException
	 * 
	 * @param  string $format      The {@link http://php.net/date date()} function compatible formatting string, or a format name from {@link fTimestamp::createFormat()}
	 * @param  string $adjustment  A temporary adjustment to make
	 * @return string  The formatted (and possibly adjusted) time
	 */
	public function format($format, $adjustment=NULL)
	{
		$format = fTimestamp::translateFormat($format);
		
		$restricted_formats = 'cdDeFIjlLmMnNoOPrStTUwWyYzZ';
		if (preg_match('#(?!\\\\).[' . $restricted_formats . ']#', $format)) {
			fCore::toss('fProgrammerException', 'The formatting string, ' . $format . ', contains one of the following non-time formatting characters: ' . join(', ', str_split($restricted_formats)));	
		}
		
		$time = $this->time;
		
		if ($adjustment) {
			$time = $this->makeAdjustment($adjustment, $time);
		}
		
		return date($format, $time);
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
			fCore::toss('fValidationException', 'The adjustment specified, ' . $adjustment . ', does not appear to be a valid relative time measurement'); 		
		}  
		
		if (!preg_match('#^\s*(([+-])?\d+(\s+(min(untes?)?|sec(onds?)?|hours?))?\s*|now\s*)+\s*$#i', $adjustment)) {
			fCore::toss('fValidationException', 'The adjustment specified, ' . $adjustment . ', appears to be a date or timezone adjustment. Only adjustments of hours, minutes and seconds are allowed for times.');	 		
		}

		return $timestamp;
	}
	
	
	/**
	 * Sets the time, changing the date on the timestamp to 1970-01-01
	 * 
	 * @param  integer $timestamp  The time to set. The date will be changed to 1970-01-01.
	 * @return void
	 */
	private function set($timestamp)
	{
		$this->time = strtotime(date('1970-01-01 H:i:s', $timestamp));   
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