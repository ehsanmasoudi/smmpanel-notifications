<?php

define('BOT_API_KEY','');
$ChannelId = "";
$ApiEndponit = "https://example.ir/api/v2/";
$ApiKey = "";


function CurlKon (string $url, array $params){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $http_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($http_response_code === 200) return json_decode($res, true);
    return false;
}

function bot($method,$datas=[]){
    $url = "https://api.telegram.org/bot".BOT_API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}
function sendMessage($chat_id, $text){
 bot('sendMessage',[
 'chat_id'=>$chat_id,
 'text'=>$text
 ]);
}


$newServices = CurlKon($ApiEndponit, [
    "key" => $ApiKey,
    "action" => "services",
]);

if($newServices === false or (isset($newServices["error"]) and $newServices["error"])) die();
if(!file_exists("services.json")){
    file_put_contents("services.json", json_encode($newServices));
}
$lastServices = json_decode(file_get_contents("services.json"), true);


$k2v_newServices = array_reduce($newServices, function ($result, $item) {
    $result[$item["service"]] = $item;
    return $result;
}, array());
$k2v_lastServices = array_reduce($lastServices, function ($result, $item) {
    $result[$item["service"]] = $item;
    return $result;
},array());



$lastIDs = array_column($lastServices, "service");
$newIDs = array_column($newServices, "service");

$jadidFaal = array_diff($newIDs, $lastIDs);
$gheyreFaal = array_diff($lastIDs,$newIDs);

foreach ($jadidFaal as $id) {
    # code - new service add notiffication...
    $price = number_format(intval($k2v_newServices[$id]["rate"]));
    $reseller_price = number_format(intval($k2v_newServices[$id]["rate"] * 0.95));
    $messageText = "
💹 #سرویس_جدید

🗂  نام دسته: {$k2v_newServices[$id]["category"]}
    
♻️ نام سرویس: {$k2v_newServices[$id]["name"]}
    
📍  شناسه سرویس: {$id}
    
💰 قیمت: {$price} تومان

💰 قیمت نمایندگان: {$reseller_price} تومان";
    sendMessage($ChannelId, $messageText);
    echo $messageText;
}

foreach ($gheyreFaal as $id) {
    # code - service disable notiffication...
    $price = number_format(intval($k2v_lastServices[$id]["rate"]));
    $reseller_price = number_format(intval($k2v_lastServices[$id]["rate"] * 0.95));
    $messageText = "
🔴  #غیر_فعال_شدن_سرویس

🗂  نام دسته: {$k2v_lastServices[$id]["category"]}
    
♻️ نام سرویس: {$k2v_lastServices[$id]["name"]}
    
📍  شناسه سرویس: {$id}
    
💰 قیمت: {$price} تومان

💰 قیمت نمایندگان: {$reseller_price} تومانs";
    sendMessage($ChannelId, $messageText);
    echo $messageText;
}


$toCheck = array_values(array_diff($lastIDs + $newIDs, array_merge($jadidFaal, $gheyreFaal)));


foreach ($toCheck as $id) {
    if($k2v_newServices[$id]["rate"] > $k2v_lastServices[$id]["rate"]){
        # price Increase...
    $last_price = number_format(intval($k2v_lastServices[$id]["rate"]));
    $price = number_format(intval($k2v_newServices[$id]["rate"]));
    $last_reseller_price = number_format(intval($k2v_lastServices[$id]["rate"] * 0.95));
    $reseller_price = number_format(intval($k2v_newServices[$id]["rate"] * 0.95));
        $messageText = "
💹 #افزایش_قیمت

🗂  نام دسته: {$k2v_newServices[$id]["category"]}
        
♻️ نام سرویس: {$k2v_newServices[$id]["name"]}
        
📍  شناسه سرویس: {$id}
        
💰 قیمت قبلی: {$last_price} تومان
✅ قیمت جدید:  {$price} تومان

💰 قیمت قبلی نمایندگان:  {$last_reseller_price} تومان
✅ قیمت جدید نمایندگان:  {$reseller_price} تومان";
        sendMessage($ChannelId, $messageText);
        echo $messageText;
    }
    else if ($k2v_newServices[$id]["rate"] < $k2v_lastServices[$id]["rate"]){
        # price decrease...
        $last_price = number_format(intval($k2v_lastServices[$id]["rate"]));
        $price = number_format(intval($k2v_newServices[$id]["rate"]));
        $last_reseller_price = number_format(intval($k2v_lastServices[$id]["rate"] * 0.95));
        $reseller_price = number_format(intval($k2v_newServices[$id]["rate"] * 0.95));
        $messageText = "
💹 #کاهش_قیمت

 🗂  نام دسته: {$k2v_newServices[$id]["category"]}
        
♻️ نام سرویس: {$k2v_newServices[$id]["name"]}
        
📍  شناسه سرویس: {$id}
        
💰 قیمت قبلی: {$last_price} تومان
✅ قیمت جدید:  {$price} تومان

💰 قیمت قبلی نمایندگان: {$last_reseller_price} تومان
✅ قیمت جدید نمایندگان:  {$reseller_price} تومان";
        sendMessage($ChannelId, $messageText);
        echo $messageText;
    }
}


file_put_contents("services.json", json_encode($newServices));