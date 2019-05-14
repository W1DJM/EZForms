<?php
namespace namespace EZForms;

/**
  *
  * Called when a form has been submitted. Parses all of the form variables
  * and adjusts them as directed by database entries.
  *
  */

class parser
{

    private $utilities = '';  // instance of our utility functions
    private $database  = '';  // instance of a connection to our database

    public function __construct($DB_Class) {

        $this->database  = $DB_Class;  // instance of the database we need will be passed
        $this->utilities = new utilities($DB_Class);

    }  // end of the __construct function

    //
    // public function to read and check a form
    //
    // Parameters Required
    //
    // formfields - the name of the form fields as it will be found in the forms_fields table
    //
    // Returns 3 values in an array:
    //
    // The first entry in the array will be a zero or 1 to indicate whether or not any
    // errors were detected. An error might be missing data in a required field or
    // non-numeric data in a numeric field for example.
    //
    // The second entry in the array will be an array with all of the form field names
    // and their values as read from the form. All of the values will have been
    // "cleaned" as directed by any values in the forms_fields_adjust table.
    //
    // The third entry in the array will be an array of the field names that are in
    // error.
    //

    public function parseform($formfields) {

        $FieldError = array();

        $col_list   = $this->utilities->get_column_list('form_fields');

        //
        // Get all of the fields for the form
        //

        $FieldsQ = $this->database->prepare('SELECT ' . $col_list . ' FROM `form_fields` ' .
                                "WHERE `form` = '$formfields' "          .
                                "ORDER BY `sequence`");
        $FieldsQ->execute();
        $FieldsD = $FieldsQ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($FieldsD as $FieldsQ) {

            $Fields[] = $FieldsQ;  // fetch a row and stash it

        }  // end of for loop through all of the data

        //
        // Get all of the field adjustments for the fields on our form
        //

        $FieldsQ = $this->database->prepare('SELECT `field`,`sequence`,`adjust` FROM `form_adjustments` ' .
                                "WHERE `form` = '$formfields' "          .
                                "ORDER BY `field`,`sequence`");
        $FieldsQ->execute();
        $FieldsD = $FieldsQ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($FieldsD as $AData) {

            $FieldA[$AData['field']][] = $AData['adjust'];  // stash for later

        }  // end of for loop through all of the data

        //
        // Get all of the field validations required for the fields on our form
        //

        $FieldsQ = $this->database->prepare('SELECT `field`,`validate` FROM `form_validations` ' .
                               "WHERE `form` = '$formfields' "          .
                               "ORDER BY `field`");
        $FieldsQ->execute();
        $FieldsD = $FieldsQ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($FieldsD as $VData) {

            $FieldV[$VData['field']] = $VData['validate'];  // stash for later

        }  // end of for loop through all of the data

        //
        // Now go through the fields on the form, retrieve values if any,
        // adjust as instructed by the fields_adjust table then do any field
        // validation as instructed by the fields_validate table while also
        // checking for required fields
        //

        $errors = 0;  // assume there won't be any errors

        foreach ($Fields as $FieldsR) {

            $fieldval = '';  // If a form field doesn't have a value this will be its placeholder

            //
            // Input type of file does not set a $_POST variable so we need to "fake it" a little
            // and set the name of the field in $_POST to whatever is contained in the name
            // position of the $_FILES array so we'll be able to check whether or not the field
            // has been completed. We will also make sure the $_FILES error code was zero and that
            // both a temporary name and an actual file name are available. It is up to the using
            // code to do further validation on the other data found in $_FILES.
            //

            if (              $FieldsR['field_type']         == 'file' AND
                isset($_FILES[$FieldsR['field']])                      AND
                      $_FILES[$FieldsR['field']]['error']    == 0      AND
                      $_FILES[$FieldsR['field']]['name']     != ''     AND
                      $_FILES[$FieldsR['field']]['tmp_name'] != '') {

                    $_POST[$FieldsR['field']] = $_FILES[$FieldsR['field']]['name'];

            }  // of if the field type is file and it looks like a file was uploaded successfully

            if (isset($_POST[$FieldsR['field']]) AND $_POST[$FieldsR['field']] != '') {

				//
				// Blindly remove any HTML tags and leading/trailing
				// blanks from the POST value
				//

                $fieldval = trim(strip_tags($_POST[$FieldsR['field']]));

                if (isset($FieldA[$FieldsR['field']])) {

                    foreach ($FieldA[$FieldsR['field']] as $FieldAdjust) {

                        $formattype = $FieldAdjust;

                        $fieldval   = $this->utilities->$formattype($fieldval);

                    }  // of foreach through the field adjustments

                }  // of if the field has adjustments needed

                if (isset($FieldV[$FieldsR['field']])) {

                    $validatetype = $FieldV[$FieldsR['field']];

                    if (!$this->utilities->$validatetype($fieldval)) {  // a 0 returned means an error

                        $errors = 1;  // turn on the error flag

                        $FieldError[$FieldsR['field']] = 1;

                    }  // end of if validation returned a 1

                }  // of if the field required validation

            }  // of if a field has any value

            //
            // If a field is required and it is blank or the type of field
            // is of the select variety and the field value is zero we are
            // missing a required field so turn on the error flag and mark
            // the field as being in error.

            if (             $FieldsR['required']    == 1                AND
                 (           $fieldval               == ''               OR
                ((strtolower($FieldsR['field_type']) == 'select'         OR
                  strtolower($FieldsR['field_type']) == 'custom-select') AND
                             $fieldval               == ''))) {

                $errors = 1;  // turn on the error flag

                $FieldError[$FieldsR['field']] = 1;

            }  // end of if blank field and it's required

            $FieldData[$FieldsR['field']] = $fieldval;

        }  // end of foreach through the fields

	    return array($errors,$FieldData,$FieldError);

    }  // end of function

}  // end of class
