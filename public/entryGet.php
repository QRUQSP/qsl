<?php
//
// Description
// ===========
// This method will return all the information about an log entry.
//
// Arguments
// ---------
// api_key:
// auth_token:
// station_id:         The ID of the station the log entry is attached to.
// entry_id:          The ID of the log entry to get the details for.
//
function qruqsp_qsl_entryGet($q) {
    //
    // Find all the required and optional arguments
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'prepareArgs');
    $rc = qruqsp_core_prepareArgs($q, 'no', array(
        'station_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Station'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Log Entry'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this station
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($q, $args['station_id'], 'qruqsp.qsl.entryGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load station settings
    //
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'intlSettings');
    $rc = qruqsp_core_intlSettings($q, $args['station_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dateFormat');
    qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'timeFormat');
    $date_format = qruqsp_core_dateFormat($q, 'php');
    $time_format = qruqsp_core_timeFormat($q, 'php');

    //
    // Return default for new Log Entry
    //
    if( $args['entry_id'] == 0 ) {
        $dt = new DateTime('now', new DateTimeZone('UTC'));
        $entry = array('id'=>0,
            'date_of_traffic'=>$dt->format($date_format),
            'time_of_traffic'=>$dt->format($time_format),
            'frequency'=>'',
            'mode'=>'0',
            'operator_id'=>'0',
            'from_call_sign'=>'',
            'from_call_suffix'=>'',
            'to_call_sign'=>'',
            'to_call_suffix'=>'',
            'traffic'=>'',
            'from_r'=>'0',
            'from_s'=>'0',
            'from_t'=>'0',
            'to_r'=>'0',
            'to_s'=>'0',
            'to_t'=>'0',
        );
    }

    //
    // Get the details for an existing Log Entry
    //
    else {
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
            . "qruqsp_qsl_entries.traffic, "
            . "qruqsp_qsl_entries.from_r, "
            . "qruqsp_qsl_entries.from_s, "
            . "qruqsp_qsl_entries.from_t, "
            . "qruqsp_qsl_entries.to_r, "
            . "qruqsp_qsl_entries.to_s, "
            . "qruqsp_qsl_entries.to_t "
            . "FROM qruqsp_qsl_entries "
            . "WHERE qruqsp_qsl_entries.station_id = '" . qruqsp_core_dbQuote($q, $args['station_id']) . "' "
            . "AND qruqsp_qsl_entries.id = '" . qruqsp_core_dbQuote($q, $args['entry_id']) . "' "
            . "";
        qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = qruqsp_core_dbHashQueryArrayTree($q, $strsql, 'qruqsp.qsl', array(
            array('container'=>'entries', 'fname'=>'id', 
                'fields'=>array('time_of_traffic', 'date_of_traffic', 'time_of_traffic', 'frequency', 'mode', 'operator_id', 
                    'from_call_sign', 'from_call_suffix', 'to_call_sign', 'to_call_suffix', 'traffic', 'from_r', 'from_s', 'from_t', 'to_r', 'to_s', 'to_t'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.7', 'msg'=>'Log Entry not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['entries'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsl.8', 'msg'=>'Unable to find Log Entry'));
        }
        $entry = $rc['entries'][0];

        //
        // Join the suffix's to the from and to
        //
        $entry['from_call'] = $entry['from_call_sign'];
        if( $entry['from_call_suffix'] != '' ) {
            $entry['from_call'] .= '/' . $entry['from_call_suffix'];
        }
        $entry['to_call'] = $entry['to_call_sign'];
        if( $entry['to_call_suffix'] != '' ) {
            $entry['to_call'] .= '/' . $entry['to_call_suffix'];
        }
    }

    return array('stat'=>'ok', 'entry'=>$entry);
}
?>
