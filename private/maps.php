<?php
//
// Description
// -----------
// This function returns the int to text mappings for the module.
//
// Arguments
// ---------
//
// Returns
// -------
//
function qruqsp_qsl_maps(&$q) {
    //
    // Build the maps object
    //
    $maps = array();
    $maps['entry'] = array('mode'=>array(
        '0'=>'Unknown',
        '10'=>'CW',
        '20'=>'LSB',
        '30'=>'USB',
        '40'=>'FM',
        '50'=>'RTTY',
        '60'=>'PSK',
        '70'=>'JT',
        '100'=>'AM',
    ));
    //
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
