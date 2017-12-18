<?php
//
// Description
// -----------
// This method will return the list of Log Entrys for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:              The ID of the tenant to get Log Entry for.
//
function qruqsp_qsl_entryList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($ciniki, $args['tnid'], 'qruqsp.qsl.entryList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the module maps
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsl', 'private', 'maps');
    $rc = qruqsp_qsl_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps']; 
    
    //
    // Load the datetimeFormat
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Get the list of entries
    //
    $strsql = "SELECT qruqsp_qsl_entries.id, "
        . "qruqsp_qsl_entries.utc_of_traffic, "
        . "DATE_FORMAT(utc_of_traffic, '%Y-%m-%d') AS date_of_traffic, "
        . "DATE_FORMAT(utc_of_traffic, '%H:%i') AS time_of_traffic, "
        . "qruqsp_qsl_entries.frequency, "
        . "qruqsp_qsl_entries.mode, "
        . "qruqsp_qsl_entries.mode AS mode_text, "
        . "qruqsp_qsl_entries.operator_id, "
        . "ciniki_users.username AS operator_callsign, "    // FIXME: ADD qruqsp_core_users tables to store callsign
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
        . "LEFT JOIN ciniki_users ON ("
            . "qruqsp_qsl_entries.operator_id = ciniki_users.id "
            . ") "
        . "WHERE qruqsp_qsl_entries.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY qruqsp_qsl_entries.utc_of_traffic DESC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qsl', array(
        array('container'=>'entries', 'fname'=>'id', 
            'fields'=>array('id', 'utc_of_traffic', 'date_of_traffic', 'time_of_traffic', 'frequency', 'mode', 'mode_text', 'operator_id', 'operator_callsign',
                'from_call_sign', 'from_call_suffix', 'to_call_sign', 'to_call_suffix', 'from_r', 'from_s', 'from_t', 'to_r', 'to_s', 'to_t'),
            'maps'=>array('mode_text'=>$maps['entry']['mode']),
            'utctotz'=>array('utc_of_traffic'=>array('timezone'=>'UTC', 'format'=>$datetime_format)),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['entries']) ) {
        $entries = $rc['entries'];
        $entry_ids = array();
        foreach($entries as $iid => $entry) {
            $entries[$iid]['from_call'] = $entry['from_call_sign'] . ($entry['from_call_suffix'] != '' ? '/' . $entry['from_call_suffix'] : '');
            $entries[$iid]['to_call'] = $entry['to_call_sign'] . ($entry['to_call_suffix'] != '' ? '/' . $entry['to_call_suffix'] : '');
            $entries[$iid]['from_rst'] = ($entry['from_r'] > 0 ? $entry['from_r'] : '?') 
                . ($entry['from_s'] > 0 ? $entry['from_s'] : '?') 
                . ($entry['from_t'] > 0 ? $entry['from_t'] : '');
            $entries[$iid]['to_rst'] = ($entry['to_r'] > 0 ? $entry['to_r'] : '?') 
                . ($entry['to_s'] > 0 ? $entry['to_s'] : '?') 
                . ($entry['to_t'] > 0 ? $entry['to_t'] : '');
            $entry_ids[] = $entry['id'];
        }
    } else {
        $entries = array();
        $entry_ids = array();
    }

    return array('stat'=>'ok', 'entries'=>$entries, 'nplist'=>$entry_ids);
}
?>
