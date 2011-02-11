<?php

if (false) $DB = new moodle_database();

/**
 * Rename a object's property if it exists.
 */
function rename_object_property($object, $oldname, $newname)
{
    if (!empty($object->{$oldname})) {
        $object->{$newname} = $object->{$oldname};
        unset($object->{$oldname});
    }
}

/**
 * Given a number of possible field to match on, find a record.
 *
 * @param string $tablename  Name of the table to SELECT into
 * @param array  $paramnames Hash of parameter name to the column name in the DB
 * @param array  $values     Hash of the values indexed by parameter name
 * @return mixed             The DB record if found, otherwise an Exception object
 */
function find_matching_record($tablename, $paramnames, $values)
{
    global $DB; // BK added this
	foreach ($paramnames as $paramname => $columnname) {
        if (!empty($values[$paramname])) {
            //if (!$record = get_record($tablename, $columnname, $values[$paramname])) { // Moodle 1.9 style 
			if (!$record = $DB->get_record($tablename, array($columnname=>$values[$paramname]))) { // Moodle 2.0 style
                return new Exception("$tablename does not exist: $columnname=".$values[$paramname]);
            }
            break; // only match the first parameter
        }
    }
    if (empty($record)) {
        return new Exception("You must provide one of these parameters to identify the $tablename: ".implode(', ', array_keys($paramnames)));
    }
    else {
        return $record;
    }
}

?>