<?php
//
// Description
// ===========
//
// Arguments
// ---------
//
function qruqsp_qsl_entryUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Log Entry'),
        'utc_of_traffic'=>array('required'=>'no', 'blank'=>'no', 'type'=>'datetime', 'name'=>'UTC Time'),
        'date_of_traffic'=>array('required'=>'no', 'blank'=>'no', 'type'=>'date', 'name'=>'Date'),
        'time_of_traffic'=>array('required'=>'no', 'blank'=>'no', 'type'=>'time', 'name'=>'Time'),
        'frequency'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Frequency'),
        'mode'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Mode'),
        'from_call'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Call Sign From'),
        'from_call_sign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Call Sign From'),
        'from_call_suffix'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Suffix From'),
        'to_call'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Call Sign To'),
        'to_call_sign'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Call Sign To'),
        'to_call_suffix'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Suffix To'),
        'traffic'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Traffic'),
        'from_r'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Readability From'),
        'from_s'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Strength From'),
        'from_t'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tone From'),
        'to_r'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Readability To'),
        'to_s'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Strength To'),
        'to_t'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Tone To'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Parse args
    //
    if( isset($args['from_call']) ) {
        if( $args['from_call'] != '' ) {
            $pieces = explode('/', $args['from_call']);
            $args['from_call_sign'] = $pieces[0];
            if( isset($pieces[1]) ) {
                $args['from_call_suffix'] = $pieces[1];
            }
        }
        else {
            $args['from_call_sign'] = '';
            $args['from_call_suffix'] = '';
        }
    }
    if( isset($args['to_call']) && $args['to_call'] != '' ) {
        if( $args['to_call'] != '' ) {
            $pieces = explode('/', $args['to_call']);
            $args['to_call_sign'] = $pieces[0];
            if( isset($pieces[1]) ) {
                $args['to_call_suffix'] = $pieces[1];
            }
        }
        else {
            $args['to_call_sign'] = '';
            $args['to_call_suffix'] = '';
        }
    }

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($ciniki, $args['tnid'], 'qruqsp.qsl.entryUpdate');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the existing entry
    //
    $strsql = "SELECT id, "
        . "DATE_FORMAT(utc_of_traffic, '%Y-%m-%d') AS date_of_traffic, "
        . "DATE_FORMAT(utc_of_traffic, '%H:%i') AS time_of_traffic "
        . "FROM qruqsp_qsl_entries "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['entry_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'qruqsp.qsl', 'entry');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['entry']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.3', 'msg'=>'Entry not found'));
    }
    $entry = $rc['entry'];

    //
    // Check if parts of traffic UTC date or time were specified
    //
    if( !isset($args['utc_of_traffic']) && (isset($args['date_of_traffic']) || isset($args['time_of_traffic'])) ) {
        $args['utc_of_traffic'] = (isset($args['date_of_traffic']) ? $args['date_of_traffic'] : $entry['date_of_traffic'])
            . ' ' .  (isset($args['time_of_traffic']) ? $args['time_of_traffic'] : $entry['time_of_traffic']);
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'qruqsp.qsl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the Log Entry in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'qruqsp.qsl.entry', $args['entry_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.qsl');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'qruqsp.qsl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'qruqsp', 'qsl');

    //
    // Update the web index if enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'hookExec');
    ciniki_core_hookExec($ciniki, $args['tnid'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qsl.entry', 'object_id'=>$args['entry_id']));

    return array('stat'=>'ok');
}
?>
