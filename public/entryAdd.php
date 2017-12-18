<?php
//
// Description
// -----------
// This method will add a new log entry for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:              The ID of the tenant to add the Log Entry to.
//
function qruqsp_qsl_entryAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'utc_of_traffic'=>array('required'=>'no', 'blank'=>'no', 'type'=>'datetime', 'name'=>'UTC Time'),
        'date_of_traffic'=>array('required'=>'no', 'blank'=>'no', 'type'=>'date', 'name'=>'Date'),
        'time_of_traffic'=>array('required'=>'no', 'blank'=>'no', 'type'=>'time', 'name'=>'Time'),
        'frequency'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Frequency'),
        'mode'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Mode'),
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
    if( !isset($args['utc_of_traffic']) ) {
        if( !isset($args['date_of_traffic']) || !isset($args['time_of_traffic']) ) {
            return array('stat'=>'fail','err'=>array('code'=>'qruqsp.qsl.9','msg'=>'You must specify UTC Time'));
        }
        $args['utc_of_traffic'] = $args['date_of_traffic'] . ' ' . $args['time_of_traffic'];
    }
    if( isset($args['from_call']) && $args['from_call'] != '' ) {
        $pieces = explode('/', $args['from_call']);
        $args['from_call_sign'] = $pieces[0];
        if( isset($pieces[1]) ) {
            $args['from_call_suffix'] = $pieces[1];
        }
    }
    if( isset($args['to_call']) && $args['to_call'] != '' ) {
        $pieces = explode('/', $args['to_call']);
        $args['to_call_sign'] = $pieces[0];
        if( isset($pieces[1]) ) {
            $args['to_call_suffix'] = $pieces[1];
        }
    }
    $args['operator_id'] = $ciniki['session']['user']['id'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($ciniki, $args['tnid'], 'qruqsp.qsl.entryAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
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
    // Add the log entry to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'qruqsp.qsl.entry', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'qruqsp.qsl');
        return $rc;
    }
    $entry_id = $rc['id'];

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
    ciniki_core_hookExec($ciniki, $args['tnid'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qsl.entry', 'object_id'=>$entry_id));

    return array('stat'=>'ok', 'id'=>$entry_id);
}
?>
