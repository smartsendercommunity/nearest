<?php

// settings
$storePosition = [ // Список координат закладів (зліва назви)
    "store1" => "50.4180106,25.740586,20",
    "store2" => "50.4184102,25.7400739",
    "store3" => "50.418763,25.7431617,17",
    "store4" => "50.4179921,25.7491675",
    "store5" => "50.4179921,25.7491675",
    "store6" => "50.4085175,25.7141094",
    "store7" => "50.4085213,25.6909397",
    "store8" => "50.4286705,25.0902867",
    "store9" => "50.4345865,25.0245404",
    "store10" => "50.0953529,25.5902555",
    "store11" => "50.121094,25.675692",
    "store12" => "50.1205209,25.6969168",
    "store13" => "50.430671,25.3418218",
    "store14" => "50.5041539,25.58914",
    "store15" => "50.5041539,25.58914",
    "store16" => "50.5087569,25.6147372",
    "store17" => "50.5097677,25.6160127",
    "store18" => "50.7496817,26.030091",
    "store19" => "50.7442378,25.9708613",
    "store20" => "50.2347508,25.769837",
];
$limit = 5; // Кількість закладів для результату
$maxDist = 10; // В результаті лише заклади до 10км

// system 
define('EARTH_RADIUS', 6372795);
header('Content-Type: application/json; charset=utf-8');
$input = json_decode(file_get_contents("php://input"), true);
// functions
function calculateTheDistance ($φA, $λA, $φB, $λB) {
    // перевести координаты в радианы
    $lat1 = $φA * M_PI / 180;
    $lat2 = $φB * M_PI / 180;
    $long1 = $λA * M_PI / 180;
    $long2 = $λB * M_PI / 180;
    // косинусы и синусы широт и разницы долгот
    $cl1 = cos($lat1);
    $cl2 = cos($lat2);
    $sl1 = sin($lat1);
    $sl2 = sin($lat2);
    $delta = $long2 - $long1;
    $cdelta = cos($delta);
    $sdelta = sin($delta);
    // вычисления длины большого круга
    $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
    $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;
    //
    $ad = atan2($y, $x);
    $dist = $ad * EARTH_RADIUS;
    return $dist;
}
function send_bearer($url, $token, $type = "GET", $param = []){
    $descriptor = curl_init($url);
     curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
     curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('User-Agent: M-Soft Integration', 'Content-Type: application/json', 'Authorization: Bearer '.$token)); 
     curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);
    $itog = curl_exec($descriptor);
    curl_close($descriptor);
    return $itog;
}


// input check
if ($input["position"] == NULL) {
    $result["state"] = false;
    $result["error"]["message"][] = "'position' is missing";
} else if (is_array($input["position"])) {
    if ($input["position"]["lat"] == NULL) {
        $result["state"] = false;
        $result["error"]["message"][] = "'position[lat]' is missing";
    } else {
        $lat2 = $input["position"]["lat"];
    }
    if ($input["position"]["long"] == NULL) {
        $result["state"] = false;
        $result["error"]["message"][] = "'position[long]' is missing";
    } else {
        $long2 = $input["position"]["long"];
    }
} else {
    $userPosition = explode(',', $input["position"]);
    $lat1 = $userPosition[0];
    $long1 = $userPosition[1];
}

if ($result["state"] === false) {
    echo json_encode($result);
    exit;
} else {
    $result["state"] = true;
}


// processing
foreach ($storePosition as $var => $data) {
    $exp = explode(",", $data);
    $lat2 = $exp[0];
    $long2 = $exp[1];
    $dist = calculateTheDistance($lat1, $long1, $lat2, $long2) / 1000;
    if ($dist <= $maxDist) {
        $update[number_format($dist, 2, ".", "")] = ["name" => $var, "position" => $data, "distance" => number_format($dist, 2, ".", "")];
    }
}
ksort($update);
$update = array_chunk($update, $limit, true)[0];
$update = array_values($update);

$result["store"] = $update;

echo json_encode($result);

