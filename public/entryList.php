<?php
//
// Description
// -----------
// This method will return the list of Log Entrys for a station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:        The ID of the station to get Log Entry for.
//
function qruqsp_qsl_entryList($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($q, $args['station_id'], 'qruqsp.qsl.entryList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of entries
    //
    $strsql = "SELECT qruqsp_qsl_entries.id, "
        . "qruqsp_qsl_entries.time_of_traffic, "
        . "qruqsp_qsl_entries.frequency, "
        . "qruqsp_qsl_entries.mode, "
        . "qruqsp_qsl_entries.operator_id, "
        . "qruqsp_qsl_entries.from_call_sign, "
        . "qruqsp_qsl_entries.from_call_suffix, "
        . "qruqsp_qsl_entries.to_call_sign, "
        . "qruqsp_qsl_entries.to_call_suffix, "
        . "qruqsp_qsl_entries.from_r, "
        . "qruqsp_qsl_entries.from_s, "
        . "qruqsp_qsl_entries.from_t, "
        . "qruqsp_qsl_entries.to_r, "
        . "qruqsp_qsl_entries.to_s, "
        . "qruqsp_qsl_entries.to_t "
        . "FROM qruqsp_qsl_entries "
        . "WHERE qruqsp_qsl_entries.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "";
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qsl', array(
        array('container'=>'entries', 'fname'=>'id', 
            'fields'=>array('id', 'time_of_traffic', 'frequency', 'mode', 'operator_id', 'from_call_sign', 'from_call_suffix', 'to_call_sign', 'to_call_suffix', 'from_r', 'from_s', 'from_t', 'to_r', 'to_s', 'to_t')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['entries']) ) {
        $entries = $rc['entries'];
        $entry_ids = array();
        foreach($entries as $iid => $entry) {
            $entry_ids[] = $entry['id'];
        }
    } else {
        $entries = array();
        $entry_ids = array();
    }

    return array('stat'=>'ok', 'entries'=>$entries, 'nplist'=>$entry_ids);
}
?>