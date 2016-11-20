<?php
//
// Description
// -----------
// This function returns the settings for the module and the main menu items and settings menu items
//
// Arguments
// ---------
// q:
// station_id:
// args: The arguments for the hook
//
function qruqsp_qsl_hooks_uiSettings(&$q, $station_id, $args) {
    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'menu_items'=>array(), 'settings_menu_items'=>array());

    //
    // Check permissions for what menu items should be available
    //
    if( isset($q['station']['modules']['qruqsp.qsl'])
        && (isset($q['station']['permissions']['operators'])
            || ($q['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>5000,
            'label'=>'qsl',
            'edit'=>array('app'=>'qruqsp.qsl.main'),
            );
        $rsp['menu_items'][] = $menu_item;
    }

    return $rsp;
}
?>
