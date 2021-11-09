<?php

use Nio01\Validator\Rule;
use Nio01\Validator\Validator;

$rules = [];


// min
$rules[] = Rule::Builder('min', function ($var, $value) {
    if (
        is_string($value)
        && !preg_match('/^\d+/', $value)
    ) $value = strlen($value);

    return $value >= $var;
});


$rules[] = Rule::Builder('bigger', function ($var, $value) {
    return $value > $var;
});

$rules[] = Rule::Builder('lower', function ($var, $value) {
    return $value < $var;
});


// max
$rules[] = Rule::Builder('max', function ($var, $value) {
    if (
        is_string($value)
        && !preg_match('/^\d+/', $value)
    ) $value = strlen($value);

    return $value <= $var;
});

/** range rule
 * -> return true if the parameter $value is in range of $value[0] and $value[1]
 */
$rules[] = Rule::Builder("in_range",  function (array $var, $value) {
    gettype($value) == "string" ? $len = strlen($value) : $len = $value;
    $r = (range((int) $var[0], (int) $var[1]));
    return in_array($len, $r);
});


/** out range rule
 * -> return true if the parameter $value is out range of $value[0] and $value[1]
 */

$rules[] = Rule::Builder("out_range",  function (array $var, $value) {
    gettype($value) == "string" ? $len = strlen($value) : $len = $value;
    $r = (range($var[0], $var[1]));
    return in_array($len, $r) === false;
});



/** range rule
 * -> return true if the parameter $value is a value of array 
 */
$rules[] = Rule::Builder("in_list", function (array $var, $value) {
    $value = strval($value);
    return in_array($value, $var);
});

//

$rules[] = Rule::Builder("pattern",  function ($var, $value) {

    $patterns = [
        'number' => '\d+',
        'digit' => '\d',
        "integer" => "\-?[0-9]+",

        "pt_name" => "[a-zA-Z\sáàâãêéèóòõôíìûúùç]+",
        "pt_alfanum" => "[a-zA-Z0-9\sáàâãêéèóòõôíìûúùç]+",
        "char" => "\w",
        'string' => '\w+',
        'alfa' => '[a-zA-Z]+',
        'alfa_text' => '[a-zA-Z\s]+',
        'text' => '\w+\+?',
        "_text" => ".+",
        "datetime-local" => "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}",
        "datetime" => "\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}",
        "date" => "\d{4}-\d{2}-\d{2}",
        'alfanum' => '[a-zA-Z0-9]+',
        'alfanum_text' => '[a-zA-Z0-9\s]+',
        "url_title" => "[\w\-\_]+",
        'uuid' => '[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9\-]{12}'
    ];

    if ($var == "email")
        return filter_var($value, FILTER_VALIDATE_EMAIL);

    if (isset($patterns[$var]))
        $var = "/^" . $patterns[$var] . "$/";
    else
        $var = "/^" . $var . "$/";

    return preg_match($var, $value) == 1;
});


$rules[] = Rule::Builder("same",  function ($data1, $data) {
    return $data1 === $data;
});

$rules[] = Rule::Builder("dif",  function ($var, $value) {
    return $var !== $value;
});


$rules[] = Rule::Builder("unique", function ($table, $field, $value) {

    try {

        $conn = Validator::getConnection();

        if (is_null($conn)) {
            throw new Exception("Validator PDO instance was not been set");
        }

        if($value == "") return true;

        $sql = "SELECT COUNT(*) FROM `$table` WHERE $field = :vv  LIMIT 1";
        if ($smt = $conn->prepare($sql)) {
            if ($smt->execute([":vv" => $value])) {
                if (is_array($result = $smt->fetch()))
                    return $result["COUNT(*)"] == 0;
            }
        }

    } catch (Exception $e) {
        die($e->getMessage());
    }
});



$rules[] = Rule::Builder("exists", function ($table, $field, $value) {
    $conn = Validator::getConnection();

    if (is_null($conn)) {
        throw new Exception("Validator PDO instance was not been set");
    }

    $sql = "SELECT COUNT(*) FROM `$table` WHERE $field = :vv LIMIT 1";

    if ($smt = $conn->prepare($sql)) {
        if ($smt->execute([":vv" => $value])) {
            if (is_array($result = $smt->fetch()))
                return $result["COUNT(*)"] > 0;
        }
    }

});


// FILES

$rules[] = Rule::Builder('max-size', function ($var, $file) {
    if (!isset($file['size'])) return false;
    return $file['size'] <= $var;
});


// MIME TYPE
$rules[] = Rule::Builder('mime', function ($var, $file) {
    if (!isset($file['type'])) return false;

    $formats = [
        'html' => ['text/html', 'application/xhtml+xml'],
        'txt' => ['text/plain'],
        'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css' => ['text/css'],
        'json' => ['application/json', 'application/x-json'],
        'jsonld' => ['application/ld+json'],
        'xml' => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf' => ['application/rdf+xml'],
        'atom' => ['application/atom+xml'],
        'rss' => ['application/rss+xml']
    ];

    if (isset($formats[$var])) {
        return in_array($file['type'], $formats[$var]);
    }

    if (strpos($var, "/") === false) {
        return strpos($file['type'], "$var/") === 0
            || strpos($file['type'], "/$var") === 0;
    }

    return $file['type'] == $var;
});


// NO UPLOAD ERRORS
$rules[] = Rule::Builder('no-error', function ($file) {
    if (!isset($file['error'])) return false;
    return $file['error'] === UPLOAD_ERR_OK;
});

// 
foreach ($rules as $obj) {
    Validator::addRule($obj);
}
