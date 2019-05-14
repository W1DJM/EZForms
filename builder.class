<?php
namespace EZForms;
use PDO;
use database;

/**
  *
  * This is the foundation class that is used to create and manipulate
  * everything related to EZ_Forms.
  *
  * <code>
  * $ourform = new builder;
  * </code>
  *
  */

class EZ_Forms {

/**
   * @var int $formcolumns How many columns the form we are working with has
   */
    private $formcolumns = 1;
/**
   * @var int $field_count Used to group fields on multi column forms
   */
    private $field_count = 0;
/**
   * @var boolean  $fieldset Flag used to indicate whether or not we have a fieldset active
   */
    private $fieldset    = false;
    private $utilities   = '';       // instance of our utility functions
    private $database    = '';       // instance of a connection to our database
/**
   * @var string[] $linksbefor  Array of form links to show before the form
   */
	private $linksbefor  = array();
/**
   * @var string[] $linksafter  Array of form links to show after the form
   */
	private $linksafter  = array();

/**
  *
  * When an instance is created establish a database connection for later use
  *
  * @return null
  *
  * @todo Move the database connection into this code instead of having a separate class
  *
  * @param string $host Database server host name - defaults to localhost
  *
  * @param string $db Name of the database that contains all of the form definitions - defaults to ez_forms
  *
  * @param string $username The user name on the host for the database that contains the form definitions
  *
  * @param string $password The password on the host for the user name for database that contains the form definitions
  *
  */

    public function __construct($username,$password,$host='localhost',$db='ez_forms') {
		
		$dsn = 'mysql:host=' . $host . ';dbname=' . $db;
        $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION ,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC       ,
                PDO::ATTR_EMULATE_PREPARES   => true                   ,
               ];
		
		try {

			$this->database = new PDO('mysql:host   =' . $host . 
				                           ';dbname =' . $db   .
				                           ';charset=utf8mb4' ,
									   $username ,
                                       $password ,
                                       $opt
                                     );

        } catch (PDOException $e) {

            error_log($e);  // log the error so it may be looked at later if necessary
            echo '<div style="width: 700px;">
                    <h3>Sorry but we ran into a problem connecting to our database.</h3>
                    The problem has been logged and support staff has been notified.
                    <h6>Please try again.</h6>
                  </div>';
            exit;

        }  // end of the catch if there was an error

        #$this->utilities = new utilities($DB_Class);
        #$this->database  = $DB_Class;  // instance of the database we need will be passed

    }  // end of the __construct function

/**
  *
  * @param string   $formname The name of the form that will be included as a hidden input field named FormName when the form is built - used when the form is processed
  *
  * @param string[] $formvalues An array of values that contains default values for the fields found when the form fields are retrieved from the database using the name specified in the formfields parameter - the array keys are the names of the fields (case sensitive) and the values for the keys are the default values for the fields
  *
  * @param string[] $formerrors An array of form field names that are in error - this will usually only be used after the form has been displayed and processed at least once
  *
  * @return void
  *
  */

    public function buildform($formname,$formvalues,$formerrors) {

        $Form = $this->utilities->get_form_details($formname);

        #$col_list = $this->utilities->get_column_list('form_fields');

        $this->formcolumns = $Form['columns'];

        //
        // Get all of the fields for the form
        //

        $Fields = $this->utilities->get_form_field_details($Form['fields']);

        echo '<div class="centered PageHead">' . $Form['title'] . '</div>'     . PHP_EOL;

		if (count($Fields) > 1) {

			echo '<div class="centered BlueBody">Fields marked with an ' . PHP_EOL;
			echo '<span class="Required">*</span> are required</div>'    . PHP_EOL;

		}  // end of if the number of fields on the form is greater than 1

		$this->get_links($Form['form']);  // retrieve any links associated with this form

		//
		// If the before form links array is not empty add the before links
		//

		if (!empty($this->linksbefor)) {

			echo '<div class="centered">' . PHP_EOL;

			foreach ($this->linksbefor as $id => $data) {

				echo '<a href="' . $data['Address'] . '" title="' .
					               $data['Name']    . '" class="fauxbutton formlinks">' .
					               $data['Name']    . '</a>' . PHP_EOL;

			}  // end of foreach through the before form links

			echo '</div>' . PHP_EOL;

		}  // end of if there was at least one before link to be shown

        $formclass = ($this->formcolumns == 1) ? 'onecol' : 'twocol';

        echo '<form class="' . $formclass . '" id="' . $Form['form'] . '" method="post" enctype="multipart/form-data" action="' . $Form['action'] . '">' . PHP_EOL;

        echo '<p class="inline"><input type="hidden" name="MAX_FILE_SIZE"  value="2000000"              /></p>' . PHP_EOL;

        echo '<p class="inline"><input type="hidden" name="FormName"       value="' . $Form['form'] . '" /></p>' . PHP_EOL;

        //
        // Add hidden input field before the rest of the form fields
        //

        foreach ($Fields as $FieldPos=>$FieldsR) {

            if (!isset($formvalues[$FieldsR['field']])) {
                $formvalues[$FieldsR['field']] = $FieldsR['field_default'];
            }

            if ($FieldsR['field_type'] != 'hidden') {
                continue;  // not a hidden field so go on to the next one
            }

            $this->hidden($FieldsR['field'],$formvalues[$FieldsR['field']]);

            unset($Fields[$FieldPos]);  // remove hidden field from array of fields

        }  // end of foreach

        //
        // Done with the hidden fields so build the rest of the form
        //

        foreach ($Fields as $FieldsR) {

            switch (strtolower($FieldsR['field_type'])) {

                case 'textarea':

                    $this->textarea($FieldsR,$formvalues,$formerrors);
                    break;

                case 'custom-select':

                    $this->select($FieldsR,$formvalues,$formerrors);
                    break;

                case 'select':

                    $this->select($FieldsR,$formvalues,$formerrors);
                    break;

                case 'yesno':

                    $this->yesno($FieldsR,$formvalues,$formerrors);
                    break;

                case 'checkbox':

                    $this->checkbox($FieldsR,$formvalues,$formerrors);
                    break;

				case 'file':

                    $this->file($FieldsR,$formvalues,$formerrors);
                    break;

                case 'text':
                case 'date':
                case 'time':
                case 'password':

                    $this->other($FieldsR,$formvalues,$formerrors);
                    break;

            }  // end of switch based on type of field

            $this->field_count++;  // bump count of fields

            if ($this->formcolumns == 2) {                   // this is a two colum form

                if ($this->field_count % 2 == 0 OR           // we've already output 2 fields or
                    $FieldsR['field_type'] == 'textarea') {  // this is a text area field

                    echo '</fieldset>' . PHP_EOL;            // close the active fieldset
                    $this->fieldset = FALSE;                 // turn off active fieldset flag

                }                                            // end of if this is a two colum form

            } else {                                         // this must be a one colum form

                echo '</fieldset>' . PHP_EOL;                // close the active fieldset
                $this->fieldset = FALSE;                     // turn off active fieldset flag

            }                                                // this is a one column form

        }  // end of foreach through what was found

        if ($this->fieldset == TRUE) {     // there is a fieldset active
            echo '</fieldset>' . PHP_EOL;  // close the active fieldset
        }                                  // end of if there is a fieldset active when we're done

        //
        // Get all of the buttons for the form
        //

        $ButtonQ = $this->database->prepare('SELECT `button`,`label` ' .
											'FROM `form_buttons` ' .
                                            "WHERE `form` = '$formname'" .
											'ORDER BY `sequence`');
        $ButtonQ->execute();
        $ButtonD = $ButtonQ->fetchAll(PDO::FETCH_ASSOC);

        //
        // now add the buttons to the form
        //

        if (!empty($ButtonD)) {  // we have at least 1 button to add to the form

            echo '<fieldset class="centered">' . PHP_EOL;

            foreach ($ButtonD as $Button) {

                echo '<button type="submit" name="' . $Button['button'] . '">' . $Button['label'] . '</button>' . PHP_EOL;

            }  // end of foreach through the buttons for the form

            echo '</fieldset>' . PHP_EOL;

        }  // end of if there was at least 1 button defined for the form

        echo '</form>'     . PHP_EOL;

		//
		// If the after form links array is not empty add the after links
		//

		if (!empty($this->linksafter)) {

			echo '<div class="centered">' . PHP_EOL;

			foreach ($this->linksafter as $id => $data) {

				echo '<a href="' . $data['Address'] . '" title="' .
					               $data['Name']    . '" class="fauxbutton formlinks">' .
					               $data['Name']    . '</a>' . PHP_EOL;

			}  // end of foreach through the after form links

			echo '</div>' . PHP_EOL;

		}  // end of if there was at least one after link to be shown

    }  // end of the buildform function

/***********************************************************************/

    //
    // add a hidden field to the form being built
    //

    private function hidden($fieldname,$fieldvalue) {

        echo '<p class="inline"><input type="hidden" name="' . $fieldname . '" value="' . $fieldvalue . '" /></p>' . PHP_EOL;

        return;

    }  // end of hidden function

    //
    // add a textarea field to the form being built
    //

    private function textarea($FieldsR,$formvalues,$formerrors) {

        $this->start_field($FieldsR,$formerrors);

        echo '<textarea class="' . $FieldsR['style']   . '" ' .
                       'id="'    . $FieldsR['field']   . '" ' .
                       'name="'  . $FieldsR['field']   . '" ' .
                       'rows="'  . $FieldsR['minsize'] . '" ' .
                       'cols="'  . $FieldsR['maxsize'] . '">' .
                       $formvalues[$FieldsR['field']]  . '</textarea>' . PHP_EOL;

	    return;

    }  // end of the textarea function

/***********************************************************************/

    //
    // add a custom select field to the form being built
    //

    private function select($FieldsR,$formvalues,$formerrors) {

        $col_list = $this->utilities->get_column_list('form_selects');

        $FieldsQ = $this->database->prepare('SELECT ' . $col_list . ' FROM `form_selects` ' .
                               "WHERE `form` = '" . $FieldsR['form']  . "' AND "      .
                                     "`name` = '" . $FieldsR['field'] . "' LIMIT 1");
        $FieldsQ->execute();
        $FieldsD = $FieldsQ->fetchAll(PDO::FETCH_ASSOC);

        if (empty($FieldsD)) {
            return;  // no select table entry so simply return now
        }

        foreach ($FieldsD as $FieldsQ) {

            $FieldsS = $FieldsQ;  // fetch a row and stash it

        }  // end of for loop through all of the data

        //
        // We need to determine whether or not more than one column is going to be displayed
        // as the value for the selection items. We will start by assuming that it is a single
        // column then change/adjust as needed.
        //

        $dispcol = '`' . $FieldsS['sel_disp_col'] . '`';  // assume single column to be displayed

        //
        // If there is a comma in the display column value we will be displaying more
        // than one column in the select display for the select value. We need to
        // parse the display value column and build a new select value for the SQL
        // query.
        //

        if (strpos($FieldsS['sel_disp_col'],',') !== FALSE) {

            $displaycols = explode(',',$FieldsS['sel_disp_col']);

            $dispcol = '';  // clear display column variable as we will be building a new one

            foreach ($displaycols as $colname) {

                $dispcol .= '`' . $colname . '`,';  // add column to the query variable

            }  // end of foreach through column names

            $dispcol = rtrim($dispcol,',');  // remove extra trailing comma

        }  // end of if there is more than 1 column to be displayed

        $SelQS = 'SELECT `' . $FieldsS['sel_value_col'] . '`,' .
                              $dispcol                        . ' '  .
                   'FROM `' . $FieldsS['sel_table']        . '`';

        if ($FieldsS['sel_where_col'] != '') {

            $SelQS .= ' WHERE `' . $FieldsS['sel_where_col'] . '` = ';

            if (!is_numeric($FieldsS['sel_where_val'])) {

                $SelQS .= "'" . $FieldsS['sel_where_val'] . "'";

            } else {

                $SelQS .= $FieldsS['sel_where_val'];

            }
        }  // end of if the where column was not blank

        if ($FieldsS['sel_ord_col'] != '') {

            $SelQS .= ' ORDER BY `' . $FieldsS['sel_ord_col'] . '` ';

            $SelQS .= $FieldsS['sel_order_dir'];

        }  // end of if the order by column was not blank

        $SelQ = $this->database->prepare($SelQS);
        $SelQ->execute();
        $OptD = $SelQ->fetchAll(PDO::FETCH_ASSOC);

        $this->start_field($FieldsR,$formerrors);

        $F_Style = $FieldsR['style'];

        if ($FieldsR['readonly']) {

            $ReadOnly = 'disabled="disabled"';

            $F_Style .= 'RO';

            echo '<p class="inline"><input type="hidden" name="' . $FieldsR['field'] . '" value="' . $formvalues[$FieldsR['field']] . '" /></p>' . PHP_EOL;

        } else {

            $ReadOnly = '';

        }

        echo '<select name="'     . $FieldsR['field']  . '" id="' . $FieldsR['field'] .
             '" size="1" class="' . $F_Style . '" ' . $ReadOnly . '>'     . PHP_EOL;

        if ($formvalues[$FieldsR['field']] == '') {

            echo '<option value="" selected="selected">-- Please select a value --</option>' . PHP_EOL;

        }

        foreach ($OptD as $OptR) {

            echo '<option value="' . $OptR[$FieldsS['sel_value_col']] . '"';

            if ($formvalues[$FieldsR['field']] == $OptR[$FieldsS['sel_value_col']]) {

                echo ' selected="selected"';

            }

            echo '>';

            if (strpos($FieldsS['sel_disp_col'],',') !== FALSE) {  // more than 1 display column value

                foreach ($displaycols as $colname) {  // we built the column array when retrieving the data

                    echo $OptR[$colname] . ' ';  // display each value separated by a space

                }  // end of foreach through column names

            } else {  // end of if there is more than 1 column to be displayed

                echo $OptR[$FieldsS['sel_disp_col']];

            }  // end of if there was only 1 column to be displayed

            echo '</option>' . PHP_EOL;

        }  // end of loop through the select items

        echo '</select>' . PHP_EOL;

	    return;

    }  // end of the select function

/***********************************************************************/

	//
    // add a simple Yes/No select field to the form being built
    //

    private function yesno($FieldsR,$formvalues,$formerrors) {

        $this->start_field($FieldsR,$formerrors);

        echo '<select name="'     . $FieldsR['field']  . '" id="' . $FieldsR['field'] .
             '" size="1" class="' . $FieldsR['style'] . '">'     . PHP_EOL;

        echo '<option value="0"';

        if ($formvalues[$FieldsR['field']] == 0) {

            echo ' selected="selected"';

        }  // of if the value of the field on the form is zero

        echo '>No</option>' . PHP_EOL;

        echo '<option value="1"';

        if ($formvalues[$FieldsR['field']] == 1) {

            echo ' selected="selected"';

        }  // of if the value of the field on the form is one

        echo '>Yes</option>' . PHP_EOL;

        echo '</select>' . PHP_EOL;

	    return;

    }  // end of the yesno function

/***********************************************************************/

    //
    // add a checkbox field to the form being built
    //

    private function checkbox($FieldsR,$formvalues,$formerrors) {

        $this->start_field($FieldsR,$formerrors);

        $checked = '';

        $F_Style = $FieldsR['style'];

        if ($FieldsR['readonly']) {

            $ReadOnly = 'readonly="readonly"';

            $F_Style .= 'RO';

        } else {

            $ReadOnly = '';

        }

        $fieldvalue = $formvalues[$FieldsR['field']];

        #if ($formvalues[$FieldsR['field']] == $OptR[$FieldsS['Select_Value_Column']]) {

        #    $checked = ' checked="checked"';

        #}

        if ($FieldsR['format'] != '' AND $fieldvalue != '') {
            $formattype = $FieldsR['format'];
            $fieldvalue = $this->utilities->$formattype($formvalues[$FieldsR['field']]);
        }

        echo '<input class="'     . $F_Style               . '" ' .
                    'type="'      . $FieldsR['field_type'] . '" ' .
                    'id="'        . $FieldsR['field']      . '" ' .
                    'name="'      . $FieldsR['field']      . '" ' .
                    $checked                               .
                    $ReadOnly                              . ' />';

        echo PHP_EOL;

	    return;

    }  // end of the checkbox function

/***********************************************************************/

	//
    // add a file field to the form being built
    //

    private function file($FieldsR,$formvalues,$formerrors) {

//
// djm
//
// Look into adding accept= for input types of file (HTML 5)
//
// <input accept="file_extension|audio/*|video/*|image/*|media_type">
//
// http://www.w3schools.com/tags/att_input_accept.asp
//
// http://www.iana.org/assignments/media-types/media-types.xhtml
//
        $this->start_field($FieldsR,$formerrors);

        $F_Style = $FieldsR['style'];

        if ($FieldsR['readonly']) {

            $ReadOnly = 'readonly="readonly"';

            $F_Style .= 'RO';

        } else {

            $ReadOnly = '';

        }

        $fieldvalue = $formvalues[$FieldsR['field']];

        if ($FieldsR['format'] != '' AND $fieldvalue != '') {
            $formattype = $FieldsR['format'];
            $fieldvalue = $this->utilities->$formattype($formvalues[$FieldsR['field']]);
        }

        echo '<input class="'     . $F_Style               . '" ' .
                    'type="'      . $FieldsR['field_type'] . '" ' .
                    'id="'        . $FieldsR['field']      . '" ' .
                    'name="'      . $FieldsR['field']      . '" ' .
                    'size="'      . $FieldsR['minsize']    . '" ' .
                    'maxlength="' . $FieldsR['maxsize']    . '" ' .
                    $ReadOnly                              . ' />';

        echo PHP_EOL;

    }  // end of the file function

/***********************************************************************/

    //
    // add other miscellaneous fields to the form being built
    //

    private function other($FieldsR,$formvalues,$formerrors) {

        $this->start_field($FieldsR,$formerrors);

        $F_Style = $FieldsR['style'];

        if ($FieldsR['readonly']) {

            $ReadOnly = 'readonly="readonly"';

            $F_Style .= 'RO';

        } else {

            $ReadOnly = '';

        }

		$required = ($FieldsR['required'] ? 'required="required"' : '');

        $fieldvalue = $formvalues[$FieldsR['field']];

        if ($FieldsR['format'] != '' AND $fieldvalue != '') {
            $formattype = $FieldsR['format'];
            $fieldvalue = $this->utilities->$formattype($formvalues[$FieldsR['field']]);
        }

// DJM

        echo '<input class="'     . $F_Style            . '" ' .
                    'type="text" ' . #'      . $FieldsR['field_type']  . '" ' .
                    'id="'        . $FieldsR['field']   . '" ' .
                    'name="'      . $FieldsR['field']   . '" ' .
                    'value="'     . $fieldvalue         . '" ' .
                    'size="'      . $FieldsR['minsize'] . '" ' .
                    'maxlength="' . $FieldsR['maxsize'] . '" ' .
					$required                           . '  ' .
                    $ReadOnly                           . ' />';

        echo PHP_EOL;

    }  // end of the other function

/***********************************************************************/

	//
    // Build and output the start of each field on the form - they all
    // share a common beginning
    //

    private function start_field($FieldsR,$formerrors) {

        if ( $this->formcolumns == 1              OR   // one column form       or
            ($this->formcolumns == 2              AND  // two column form       and
            ($FieldsR['field_type'] == 'textarea' OR   // this is a text area   or
             $this->field_count % 2 == 0          OR   // we've output 2 fields or
             $this->fieldset == FALSE))) {             // fieldset flag isn't active

            if ($this->fieldset == TRUE) {             // if there is a fieldset active
                echo '</fieldset>' . PHP_EOL;          // close it before starting a new one
            }                                          // end of if there is a fieldset active

            echo '<fieldset>' . PHP_EOL;               // start a new fieldset
            $this->fieldset = TRUE;                    // turn on the fieldset active flag

        }                                              // end of if conditions to start a new fieldset were met

        echo '<label for="' . $FieldsR['field'] . '">';

        if (isset($formerrors[$FieldsR['field']])) {
            echo '<span class="ErrorPoint">>>></span> ';
            echo '<span class="ErrorLabel">';
        }

        echo $FieldsR['header'];

        if (isset($formerrors[$FieldsR['field']])) {
            echo '</span> ';
        }

        if ($FieldsR['required']) {
            echo '<span class="Required">*</span>';
        }

        echo '</label>' . PHP_EOL;

        return;

    }  // end of the start_field function

/***********************************************************************/

	//
	// Retrieve any external links that may be defined for this form and
	// build internal arrays with the necessary data
	//

	private function get_links($formname) {

		$col_list = $this->utilities->get_column_list('form_links');

		$LinksQ = $this->database->prepare("SELECT $col_list FROM `form_links`  "    .
										   "WHERE `form` = '" . $formname . "' " .
				                           "ORDER BY sequence");
		$LinksQ->execute();

		$TheLinks = $LinksQ->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($TheLinks)) {  // we have at least 1 link for this form

			foreach ($TheLinks as $LinkID => $LinkData) {

				if ($LinkData['show_before']) {

					$this->linksbefor[] = array('Name'    => $LinkData['name']    ,
						                        'Address' => $LinkData['address'] ,
												'Parm1'   => $LinkData['parm_1']  ,
												'Parm2'   => $LinkData['parm_2']  ,
												'Parm3'   => $LinkData['parm_3']  ,
												'Parm4'   => $LinkData['parm_4']  ,
												'Parm5'   => $LinkData['parm_5']  ,
												);

				}  // end of if this is a show before link

				if ($LinkData['show_after']) {

					$this->linksafter[] = array('Name'    => $LinkData['name']    ,
						                        'Address' => $LinkData['address'] ,
												'Parm1'   => $LinkData['parm_1']  ,
												'Parm2'   => $LinkData['parm_2']  ,
												'Parm3'   => $LinkData['parm_3']  ,
												'Parm4'   => $LinkData['parm_4']  ,
												'Parm5'   => $LinkData['parm_5']  ,
											    );

				}  // end of if this is a show before link

			}  // end of foreach through the links for this form

		}  // end of if there is at least 1 link for this form

	}  // end of the get_links function

/***********************************************************************/

    //
    // Custom selects and custom checkbox fields both use data from the
    // forms_fields_select table. This function does all of the data
    // gathering and parsing then sends back an array that is used to
    // actually build the form elements (select or checkbox).
    //

    private function select_common($FieldsR) {

		$col_list = $this->utilities->forms_get_column_list('forms_fields_select');

		$FieldsQ = $this->database->prepare('SELECT '    . $col_list . ' FROM `forms_fields_select` ' .
											"WHERE `Field_Form` = '" . $FieldsR['Field_Form'] . "' AND "      .
												  "`Field_Name` = '" . $FieldsR['Field_Name'] . "' LIMIT 1");
		$FieldsQ->execute();
		$FieldsD = $FieldsQ->fetchAll(PDO::FETCH_ASSOC);

		if (empty($FieldsD)) {
			return;  // no select table entry so simply return now
		}

		foreach ($FieldsD as $FieldsQ) {

			$FieldsS = $FieldsQ;  // fetch a row and stash it

		}  // end of for loop through all of the data

		//
		// We need to determine whether or not more than one column is going to be displayed
		// as the value for the selection items. We will start by assuming that it is a single
		// column then change/adjust as needed.
		//

		$dispcol = '`' . $FieldsS['Select_Display_Column'] . '`';  // assume single column to be displayed

		//
		// If there is a comma in the display column value we will be displaying more
		// than one column in the select display for the select value. We need to
		// parse the display value column and build a new select value for the SQL
		// query.
		//

		if (strpos($FieldsS['Select_Display_Column'],',') !== FALSE) {

			$displaycols = explode(',',$FieldsS['Select_Display_Column']);

			$dispcol = '';  // clear display column variable as we will be building a new one

			foreach ($displaycols as $colname) {

	            $dispcol .= '`' . $colname . '`,';  // add column to the query variable

		    }  // end of foreach through column names

			$dispcol = rtrim($dispcol,',');  // remove extra trailing comma

		}  // end of if there is more than 1 column to be displayed

		$SelQS = 'SELECT `' . $FieldsS['Select_Value_Column'] . '`,' .
			                  $dispcol                        . ' '  .
				   'FROM `' . $FieldsS['Select_Table']        . '`';

		if ($FieldsS['Select_Where_Column'] != '') {

			$SelQS .= ' WHERE `' . $FieldsS['Select_Where_Column'] . '` = ';

			if (!is_numeric($FieldsS['Select_Where_Value'])) {

				$SelQS .= "'" . $FieldsS['Select_Where_Value'] . "'";

			} else {

				$SelQS .= $FieldsS['Select_Where_Value'];

			}

		}  // end of if the where column was not blank

		if ($FieldsS['Select_OrderBy_Column'] != '') {

			$SelQS .= ' ORDER BY `' . $FieldsS['Select_OrderBy_Column'] . '` ';

			$SelQS .= $FieldsS['Select_OrderBy_UpDown'];

		}  // end of if the order by column was not blank

		$SelQ = $this->database->prepare($SelQS);
		$SelQ->execute();
		$OptD = $SelQ->fetchAll(PDO::FETCH_ASSOC);

		return array($FieldsS,$OptD);  // send back the details of the things to be shown

    }  // end of the select_common function

/***********************************************************************/

}  // end of class
