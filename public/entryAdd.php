<?php
//
// Description
// -----------
// This method will add a new log entry for the station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:        The ID of the station to add the Log Entry to.
//
function qruqsp_qsl_entryAdd(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
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
    $args['operator_id'] = $q['session']['user']['id'];

    //
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($q, $args['station_id'], 'qruqsp.qsl.entryAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Start transaction
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionStart');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionRollback');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionCommit');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbAddModuleHistory');
    $rc = qruqsp_core_dbTransactionStart($q, 'qruqsp.qsl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the log entry to the database
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectAdd');
    $rc = qruqsp_core_objectAdd($q, $args['station_id'], 'qruqsp.qsl.entry', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.qsl');
        return $rc;
    }
    $entry_id = $rc['id'];

    //
    // Commit the transaction
    //
    $rc = qruqsp_core_dbTransactionCommit($q, 'qruqsp.qsl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the station modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'updateModuleChangeDate');
    qruqsp_core_updateModuleChangeDate($q, $args['station_id'], 'qruqsp', 'qsl');

    //
    // Update the web index if enabled
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'hookExec');
    qruqsp_core_hookExec($q, $args['station_id'], 'qruqsp', 'web', 'indexObject', array('object'=>'qruqsp.qsl.entry', 'object_id'=>$entry_id));

    return array('stat'=>'ok', 'id'=>$entry_id);
}
?>
