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
    $maps['object'] = array('field'=>array(
        'int'=>'text',
    ));
    //
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
