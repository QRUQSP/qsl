<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an log entry.
// This method is typically used by the UI to display a list of changes that have occured
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station to get the details for.
// entry_id:          The ID of the log entry to get the history for.
// field:                   The field to get the history for.
//
function qruqsp_qsl_entryHistory($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Log Entry'),
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($q, $args['station_id'], 'qruqsp.qsl.entryHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbGetModuleHistory');
    return qruqsp_core_dbGetModuleHistory($q, 'qruqsp.qsl', 'qruqsp_qsl_history', $args['station_id'], 'qruqsp_qsl_entries', $args['entry_id'], $args['field']);
}
?>
