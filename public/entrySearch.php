<?php
//
// Description
// -----------
// This method searchs for a Log Entrys for a station.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station to get Log Entry for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
function qruqsp_qsl_entrySearch($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to station_id as owner, or sys admin.
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($q, $args['station_id'], 'qruqsp.qsl.entrySearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of entries
    //
    $strsql = "SELECT qruqsp_qsl_entries.id, "
        . "qruqsp_qsl_entries.utc_of_traffic, "
        . "DATE_FORMAT(utc_of_traffic, '%Y-%m-%d') AS date_of_traffic, "
        . "DATE_FORMAT(utc_of_traffic, '%H:%i') AS time_of_traffic, "
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
        . "LEFT JOIN qruqsp_core_users ON ("
            . "qruqsp_qsl_entries.operator_id = qruqsp_core_users.id "
            . ") "
        . "WHERE qruqsp_qsl_entries.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
        . "AND ("
            . "from_call_sign LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
            . "OR to_call_sign LIKE '" . qruqsp_core_dbQuote($q, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . qruqsp_core_dbQuote($q, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qsl', array(
        array('container'=>'entries', 'fname'=>'id', 
            'fields'=>array('id', 'utc_of_traffic', 'date_of_traffic', 'time_of_traffic', 'frequency', 'mode', 'operator_id', 
                'from_call_sign', 'from_call_suffix', 'to_call_sign', 'to_call_suffix', 'from_r', 'from_s', 'from_t', 'to_r', 'to_s', 'to_t')),
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
