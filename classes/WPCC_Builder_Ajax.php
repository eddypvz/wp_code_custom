<?php
namespace wp_code_custom;
use WPCC_DataRetrieverSource;

// Ajax for chosen source
add_action('wp_ajax_wpcc_builder_ajax_source', function () {

    $field = $_GET['field'] ?? false;
    $search = $_GET['s'] ?? '';
    $initialValue = (!empty($_GET['i']) && $_GET['i'] == 1) ? $_GET['i'] : 0;
    $dataResponse = [];

    $treeFields = entity_get::instance()->getTree();

    if (!empty($treeFields[$field])) {
        if ($treeFields[$field]['source'] instanceof WPCC_DataRetrieverSource) {

            // override search if is an initial value
            $argsOverride = [];
            if ( $initialValue ) {
                $argsOverride = [
                    'post_id' => $search
                ];
            }

            $dataTMP = $treeFields[$field]['source']->get($treeFields[$field]['source_field_key'], $treeFields[$field]['source_field_to_show'], $argsOverride);

            foreach ($dataTMP as $key => $value) {
                $dataResponse[] = ['key' => $key, 'value' => $value];
            }
        }
    }
    header( "Content-Type: application/json" );
    print json_encode($dataResponse);
    exit();
});