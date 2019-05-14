<?php
namespace EZForms;
use PDO;

/**
  *
  * Various helper functions used by the EZ_Forms package.
  *
  */

class utilities {

    private $phone_cc_fix = array('-',' ','.','(',')',',','$','*');
    private $currency_fix = array('-',' ','(',')',',','$','*');
    private $database     = '';  // instance of a connection to our database

    public function __construct($DB_Class) {

        $this->database = $DB_Class;  // instance of the database we need will be passed

    }  // end of the __construct function

    // removes leading and trailing spaces and newlines
    public function trimall($string) {
        return trim($string);}

    // removes HTML and PHP tags
    public function striptags($string) {
        return strip_tags($string);}

    // forces all characters to lower case
    public function lowerall($string) {
	    return strtolower($string);}

    // makes the first letter of each word in the supplied string upper case
    public function upperwords($string) {
	    return ucwords($string);}

    // forces all characters to upper case
    public function upperall($string) {
	    return strtoupper($string);}

    // forces all characters to upper case
    public function upperfirst($string) {
	    return ucfirst($this->lowerall($string));}

    // converts a string of 10 numbers to US formatted phone number (xxx) yyy-zzzz
    public function format_us_phone_number($number) {
        return (is_numeric($number) ? preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $number) : '');
    }  // End of format_us_phone_number function

    // format a date in YYYY-MM-DD format to a "nicer" US format of DD Mon YYYY
    public function format_us_date($string) {
        $TheDate = DateTime::createFromFormat('Y-m-d',$string);
        return ($TheDate == '' ? $string : $TheDate->format('d M Y'));
    }  // end of format_us_date function

    // format a date and time in YYYY-MM-DD HH:MM:SS format to a "nicer" US format of DD Mon YYYY HH:MM AM/PM
    public function format_us_date_time($string) {
        $TheDate = DateTime::createFromFormat('Y-m-d H:i:s',$string);
        return ($TheDate == '' ? $string : $TheDate->format('d M Y g:iA'));
    }  // end of format_us_date_time function

    // removes any punctuation entered in a dollar value field
    public function clean_dollar_value($string) {
	    return str_replace($this->currency_fix,'',$string);}

    // format a dollar value without any puncuation into a US format value with dollar sign, decimals and commas
    public function format_us_dollars($string) {
        $string = str_replace($this->currencty_fix,'',$string);
        return (is_numeric($string) ? '$' . number_format($string) : $string);}

    // removes any punctuation entered in a phone number field
    public function cleanphone($string) {
	    return str_replace($this->phone_cc_fix,'',$string);}

    // removes any punctuation entered in a credit card number field
    public function cleancc($string) {
	    return str_replace($this->phone_cc_fix,'',$string);}

    // sanitizes and validates whether or not the passed string is likely to be a valid e-mail address
    public function validate_email_address($string) {
        return (filter_var(filter_var($string,FILTER_SANITIZE_EMAIL),FILTER_VALIDATE_EMAIL) ? 1 : 0);}

    // make sure a date entered is valid
    public function validate_date($string) {
        return (strtotime($string) ? 1 : 0);}

    // make sure a time entered is valid
    public function validate_time($string) {
        return (strtotime($string) ? 1 : 0);}

    // take a valid date and return it in YYYY-MM-DD format for SQL
    public function SQL_format_date($string) {
        return (strtotime($string) ? date('Y-m-d',strtotime($string)) : $string);}

    // Take a time in 24 hour format and return it in "American" style
    public function Time24to12($stime) {
        return date("g:i", strtotime($stime));
    }  // end of the Time24to12 function

    // Take a time in "American" style and return it in 24 hour format
    public function Time12to24($stime) {
        return date("H:i", strtotime($stime));
    }  // end of the Time12to24 function

    // validates that the value passed contains only numbers
    public function numeric($value) {
        return (ctype_digit((string)$value) ? 1 : 0);}

    // validates that the value passed is numeric
    public function number($value) {
        return (is_numeric($value) ? 1 : 0);}

    // validates that the value passed is alpha only
    public function alpha($value) {
        return (ctype_alpha((string)$value) ? 1 : 0);}

    //
    // retrieve and return a summary of available forms
    //

    public function GetFormSummary() {

        $col_list = $this->get_column_list('form_forms',$this->database);

        $TheQ  = $this->database->prepare("SELECT $col_list FROM `form_forms` " .
                                         "ORDER BY `form`");
        $TheQ->execute();
        $TheD  = $TheQ->fetchAll(PDO::FETCH_ASSOC);

		return $TheD;  // send back the details about the forms on file

    }  // end of the GetOrderDetails function

    //
    // Returns a list of column names from the table name that is passed - the list
    // returned is suitable for use in a select SQL statement to retrieve all of
    // the columns in the table
    //

    public function get_column_list($col_table) {

        $collist = '';  // default to a empty column list in case something goes wrong
        $ColsQ   = $this->database->prepare('SHOW COLUMNS FROM `' . $col_table . '`');
        $ColsQ->execute();
        $ColsD   = $ColsQ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ColsD as $Column) {
            $collist .= '`' . $Column['Field'] . '`,';
        }

        return trim($collist,',');

    }  // End of get_column_list function

    //
    // Returns an array with details about a defined form. The name of the form will be
    // passed.
    //

    public function get_form_details($formname) {

        $col_list = $this->get_column_list('form_forms');

		//
        // Get all of the fields for the form
        //

        $FormQ = $this->database->prepare("SELECT $col_list FROM `form_forms` WHERE `form` = '$formname'");
        $FormQ->execute();

        return $FormQ->fetch(PDO::FETCH_ASSOC);

    }  // End of get_form_details function

    //
    // Returns an array of the field names and their settings that are used on
    // a specific form whose name is passed when called
    //

    public function get_form_field_details($formname) {

        $Fields   = array();  // default to a blank array in case something goes wrong

        $col_list = $this->get_column_list('form_fields');

        //
        // Get all of the fields for the form
        //

        $FieldsQ = $this->database->prepare('SELECT ' . $col_list . ' FROM `form_fields` ' .
                                "WHERE `form` = '$formname' "           .
                                "ORDER BY `sequence`");
        $FieldsQ->execute();
        $FieldsD = $FieldsQ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($FieldsD as $FieldsQ) {

            $Fields[] = $FieldsQ;  // stash a row of data

        }  // end of for loop through all of the data

        return $Fields;

    }  // End of get_form_field_details function

    //
    // Return the proper PDO value for binding based on the variable passed.
    //

     public function PDO_bind($variable) {

        if(is_numeric($variable)) return PDO::PARAM_INT;
        if(is_bool($variable))    return PDO::PARAM_BOOL;
        if(is_null($variable))    return PDO::PARAM_NULL;

        return PDO::PARAM_STR;

    }  // End of set_PDO_bind function

    //
    // Writes an entry to the transaction log table in the database
    //

    public function write_log($Nick='',$Success=0) {

        $LogQ = $this->database->prepare('INSERT INTO `forms_trans_log` SET      ' .
                                                     '`Success`   = :Success  , ' .
                                                     '`Nickname`  = :Nickname , ' .
                                                     '`User_IP`   = :UserIP   , ' .
                                                     '`User_Agent`= :UAgent');

        $UserIP = (isset($_SERVER['REMOTE_ADDR'])     ? $_SERVER['REMOTE_ADDR']     : NULL);
        $UAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : NULL);

        $LogQ->bindValue(':Success' ,$Success,$this->PDO_bind($Success));
        $LogQ->bindValue(':Nickname',$Nick   ,$this->PDO_bind($Nick));
        $LogQ->bindValue(':UserIP'  ,$UserIP ,$this->PDO_bind($UserIP));
        $LogQ->bindValue(':UAgent'  ,$UAgent ,$this->PDO_bind($UAgent));

        $LogQ->execute();  // write a transaction to the log table

    }  // End of the write_log function

    //
    // Return a string with US States listed in proper HTML select format.
    // The only, optional, parameter is a state abbreviation that should
	// be marked as being selected. If no parameter is passed an invalid
	// abbreviation of 0 will be first on the list with a prompt to
	// select a state from the list.
    //

    public function Load_US_State_Select($Sel='') {

        $SBack = '<select name="State" id="State" size="1" class="SelectOne">' . PHP_EOL;

        if ($Sel == "" or $Sel == 0) {  # nothing passed to us or zero passed

            $SBack .= '<option value="0" selected="selected">-- Select A State --</option>' . PHP_EOL;

		}  // of if nothing previously selected

        $col_list = $this->get_column_list('form_usstate',$this->database);
        $StateQ   = $this->database->prepare("SELECT $col_list FROM form_usstate " .
	                                         "ORDER BY abbreviation");
        $StateQ->execute();

        $StateD = $StateQ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($StateD as $Data) {

            $SBack .= '<option value="' . $Data['abbreviation'] . '"';

            if ($Data['abbreviation'] == $Sel) {

                $SBack .= ' selected="selected"';

            }  // end of if this is a realty that had already been selected

            $SBack .= '>' . $Data['state'] . '</option>' . PHP_EOL;

        }  // end of for loop through all of the data

        $SBack .= '</select>' . PHP_EOL;

        return $SBack;

    }  // End of the Load_US_State_Select function

    //
    // User Time takes a time, splits it into hours and minutes then reassembles it
    // into civilian hours and minutes with proper adjustment for single digit hours.
    //

    public function UserTime($stime) {

        list($TimH,$TimM) = explode(':',$stime);  // break the time passed into hours and minutes

        $STimH = date('g',mktime($TimH,0,0,0,0));

            if (strlen($STimH) == 1) {        // if the starting time hour is a single digit
            $STimH = '&nbsp;' . $STimH;}  // pad it on the left with a required space

        return $STimH . date(':i',mktime(0,$TimM,0,0,0));

    }  // end of the UserTime function

}  // end of class
