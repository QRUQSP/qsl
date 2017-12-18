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
// tnid:               The ID of the tenant the log entry is attached to.
// entry_id:          The ID of the log entry to get the details for.
//
function qruqsp_qsl_entryGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'entry_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Log Entry'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsl', 'private', 'checkAccess');
    $rc = qruqsp_qsl_checkAccess($ciniki, $args['tnid'], 'qruqsp.qsl.entryGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'timeFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');
    $time_format = ciniki_users_timeFormat($ciniki, 'php');

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
            . "WHERE qruqsp_qsl_entries.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND qruqsp_qsl_entries.id = '" . ciniki_core_dbQuote($ciniki, $args['entry_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qsl', array(
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
