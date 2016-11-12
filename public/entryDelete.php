<?php
//
// Description
// -----------
// This method will delete an log entry.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:            The ID of the station the log entry is attached to.
// entry_id:            The ID of the log entry to be removed.
//
function qruqsp_qsl_entryDelete(&$q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Log Entry'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($q, $args['station_id'], 'qruqsp.qsl.entryDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the log entry
    //
    $strsql = "SELECT id, uuid "
        . "FROM qruqsp_qsl_entries "
        . "WHERE station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND id = '" . qruqsp_core_dbQuote($q, $args['entry_id']) . "' "
        . "";
    $rc = qruqsp_core_dbHashQuery($q, $strsql, 'qruqsp.qsl', 'entry');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['entry']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.4', 'msg'=>'Log Entry does not exist.'));
    }
    $entry = $rc['entry'];

    //
    // Check for any dependencies before deleting
    //

    //
    // Check if any modules are currently using this object
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectCheckUsed');
    $rc = qruqsp_core_objectCheckUsed($q, $args['station_id'], 'qruqsp.qsl.entry', $args['entry_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.5', 'msg'=>'Unable to check if the log entry is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.6', 'msg'=>'The log entry is still in use. ' . $rc['msg']));
    }

    //
    // Start transaction
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionStart');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionRollback');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbTransactionCommit');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbDelete');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectDelete');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbAddModuleHistory');
    $rc = qruqsp_core_dbTransactionStart($q, 'qruqsp.qsl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the entry
    //
    $rc = qruqsp_core_objectDelete($q, $args['station_id'], 'qruqsp.qsl.entry',
        $args['entry_id'], $entry['uuid'], 0x04);
    if( $rc['stat'] != 'ok' ) {
        qruqsp_core_dbTransactionRollback($q, 'qruqsp.qsl');
        return $rc;
    }

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

    return array('stat'=>'ok');
}
?>
