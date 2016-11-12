<?php
//
// Description
// -----------
// This function returns the list of objects for the module.
//
// Arguments
// ---------
//
// Returns
// -------
//
function qruqsp_qsl_objects(&$q) {
    //
    // Build the objects
    //
    $objects = array();
    $objects['entry'] = array(
        'name'=>'Log Entry',
        'o_name'=>'entry',
        'o_container'=>'entries',
        'sync'=>'yes',
        'table'=>'qruqsp_qsl_entries',
        'fields'=>array(
            'utc_of_traffic'=>array('name'=>'Time'),
            'frequency'=>array('name'=>'Frequency'),
            'mode'=>array('name'=>'Mode'),
            'operator_id'=>array('name'=>'Operator', 'ref'=>'qruqsp.core.users', 'default'=>'0'),
            'from_call_sign'=>array('name'=>'Call Sign From', 'default'=>''),
            'from_call_suffix'=>array('name'=>'Suffix From', 'default'=>''),
            'to_call_sign'=>array('name'=>'Call Sign To', 'default'=>''),
            'to_call_suffix'=>array('name'=>'Suffix To', 'default'=>''),
            'traffic'=>array('name'=>'Traffic', 'default'=>''),
            'from_r'=>array('name'=>'Readability From', 'default'=>'0'),
            'from_s'=>array('name'=>'Strength From', 'default'=>'0'),
            'from_t'=>array('name'=>'Tone From', 'default'=>'0'),
            'to_r'=>array('name'=>'Readability To', 'default'=>'0'),
            'to_s'=>array('name'=>'Strength To', 'default'=>'0'),
            'to_t'=>array('name'=>'Tone To', 'default'=>'0'),
        ),
        'history_table'=>'qruqsp_qsl_history',
    );
    //
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
