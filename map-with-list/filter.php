<?php
// for earlier versions of PHP
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle)
    {
        return empty($needle) || strpos($haystack, $needle) !== false;
    }
}

if (isset($_POST['filter'])) {

    function removeAccents($string)
    {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'))), ' '));
    }

    $filter = trim(strtolower(removeAccents($_POST['filter'])));
    $filter_without_special_chars = str_replace(["-", '.'], "", trim(strtolower(removeAccents($_POST['filter']))));

    $csv = array_map('str_getcsv', file('CSV.csv'));

    $json = [];

    for ($row = 0; $row < count($csv); $row++) {
        // ignore first line
        if ($row > 0) {

            // id, city, uf, state, address, cep, phone, email, pabx, segment, complement, lat, log
            $city = trim(strtolower(removeAccents($csv[$row][1])));
            $uf = trim(strtolower(removeAccents($csv[$row][2])));
            $state = trim(strtolower(removeAccents($csv[$row][3])));
            $cep = trim(strtolower(removeAccents($csv[$row][5])));
            $cep_without_special_chars = str_replace(["-", '.'], "", trim(strtolower(removeAccents($csv[$row][5]))));

            if (
                str_contains($city, $filter) ||
                str_contains($uf, $filter) ||
                str_contains($state, $filter) ||
                str_contains($cep, $filter) ||
                str_contains($cep_without_special_chars, $filter_without_special_chars)
            ) {
                array_push($json, array(
                    "id" => $csv[$row][0],
                    "city" => $csv[$row][1],
                    "uf" => $csv[$row][2],
                    "state" => $csv[$row][3],
                    "address" => $csv[$row][4],
                    "cep" => $csv[$row][5],
                    "phone" => $csv[$row][6],
                    "email" => $csv[$row][7],
                    "pabx" => $csv[$row][8],
                    "segment" => $csv[$row][9],
                    "complement" => $csv[$row][10],
                    "lat" => trim($csv[$row][11]),
                    "lng" => trim($csv[$row][12])
                ));
            }
        }
    }

    echo json_encode($json);
} else {
    echo '{}';
}