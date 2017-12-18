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
// tnid:                  The ID of the tenant the log entry is attached to.
// entry_id:            The ID of the log entry to be removed.
//
function qruqsp_qsl_entryDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Log Entry'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($ciniki, $args['tnid'], 'qruqsp.qsl.entryDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the log entry
    //
    $strsql = "SELECT id, uuid "
        . "FROM qruqsp_qsl_entries "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['entry_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'qruqsp.qsl', 'entry');
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectCheckUsed');
    $rc = ciniki_core_objectCheckUsed($ciniki, $args['tnid'], 'qruqsp.qsl.entry', $args['entry_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.5', 'msg'=>'Unable to check if the log entry is still being used.', 'err'=>$rc['err']));
    }
    if( $rc['used'] != 'no' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.6', 'msg'=>'The log entry is still in use. ' . $rc['msg']));
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'qruqsp.qsl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the entry
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'qruqsp.qsl.entry',
        $args['entry_id'], $entry['uuid'], 0x04);
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

    return array('stat'=>'ok');
}
?>
