<?php
function WPCC_ResponseStructure($status, $msg, $array = [], $errorCode = '', $httpCode = 200) {
    $arrReturn = [];
    $arrReturn['status'] = ($status) ? 1 : 0; //para siempre devolver un entero
    $arrReturn['msg'] = $msg;

    if (!$status && $errorCode !== '') {
        $arrReturn['msg'] = "{$msg} - ({$errorCode})";
    }

    if (!is_array($array)) {
        $array = [];
    }

    $arrReturn['data'] = $array;
    $arrReturn['httpCode'] = $httpCode;
    return $arrReturn;
}

function WPCC_ResponseSuccess($msg, $array = [], $httpCode = 200, $printJson = false) {
    if ($printJson) {
        header("Content-Type: text/json");
        print json_encode(WPCC_ResponseStructure(true, $msg, $array));
        die();
    }
    else {
        return WPCC_ResponseStructure(true, $msg, $array);
    }
}

function WPCC_ResponseError($errorCode, $msg, $array = [], $httpCode = 200, $printJson = false) {
    if ($printJson) {
        header("Content-Type: text/json");
        print json_encode(WPCC_ResponseStructure(false, $msg, $array, $errorCode, $httpCode));
        die();
    }
    else {
        return WPCC_ResponseStructure(false, $msg, $array, $errorCode, $httpCode);
    }
}