<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php'); //подключение функционала bitrix для дальнейшей работы с бд

// Подключение к бд через bitrix
$connection = Bitrix\Main\Application::getConnection();
//$sqlHelper = $connection->getSqlHelper();

$input = "dcciii"; //входная строка
$data = []; // выгрузка данных из базы по всем продуктам
$result = []; // результирующий массив
$codes_array = []; // массив кодов из входной строки

foreach(array_unique(str_split($input)) as $k=>$code){
    $codes_array[] = $code;
}
$codes_array = implode("','", $codes_array);
$sql = "SELECT T2.id, T1.code, T1.title as type, T2.title, T2.price FROM ingredient_type as T1 JOIN ingredient as T2 on T1.id = T2.type_id WHERE T1.code in ('".$codes_array."')";
$recordset = $connection->query($sql);

//построчная обработка результата запроса и создание массива дата с выгруженными данными
while($res = $recordset->fetch()){  // $res - одна строка полученной таблицы в виде массива (array(4) { ["code"]=> string(1) "d" ["type"]=> string(10) "Тесто" ["title"]=> string(23) "Тонкое тесто" ["price"]=> string(6) "100.00" })
    $data[$res['code']][] = $res;
}

foreach (str_split($input) as $v) {
    $result = prepareProducts($result, $data[$v]);
}
foreach($result as $k=>$res){
    $result[$k]['products'] = array_values($res['products']);
}
$result = array_values($result);

echo(json_encode($result, JSON_UNESCAPED_UNICODE));

function prepareProducts(array $result, array $ingredients): array
{
    $temp_result = [];

    if (empty($result)) {
        foreach ($ingredients as $ing) {
            $new_product = [];
            $temp = array('type' => $ing['type'], 'value' => $ing['title']);
            $new_product['products'][$ing['id']] = $temp;
            $new_product['price'] = $ing['price'];
            $temp_result[] = $new_product;
        }
    }
    else {
        foreach ($result as $product) {
            foreach ($ingredients as $ing) {
                $new_product = [];
                if(isset($product['products'][$ing['id']])){
                    continue;
                }
                else {
                    $temp = array('type' => $ing['type'], 'value' => $ing['title']);
                    $new_product['products'] =  $product['products'];
                    $new_product['products'][$ing['id']] =  $temp;
                    $new_product['price'] = $product['price'] + $ing['price'];
                    ksort($new_product['products']);
                    $id = "";
                    foreach($new_product['products'] as $k=>$v){
                        $id .= $k;
                    }
                    $temp_result[$id] = $new_product;
                }
            }
        }
    }
    return $temp_result;
}


//пример для dcciii

//[{"products":[{"type":"Тесто","value":"Тонкое тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Грибы"}],"price":335},{"products":[{"type":"Тесто","value":"Тонкое тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Томаты"}],"price":295},{"products":[{"type":"Тесто","value":"Тонкое тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Грибы"},{"type":"Начинка","value":"Томаты"}],"price":310},{"products":[{"type":"Тесто","value":"Тонкое тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Грибы"},{"type":"Начинка","value":"Томаты"}],"price":315},{"products":[{"type":"Тесто","value":"Пышное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Грибы"}],"price":345},{"products":[{"type":"Тесто","value":"Пышное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Томаты"}],"price":305},{"products":[{"type":"Тесто","value":"Пышное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Грибы"},{"type":"Начинка","value":"Томаты"}],"price":320},{"products":[{"type":"Тесто","value":"Пышное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Грибы"},{"type":"Начинка","value":"Томаты"}],"price":325},{"products":[{"type":"Тесто","value":"Ржаное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Грибы"}],"price":385},{"products":[{"type":"Тесто","value":"Ржаное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Томаты"}],"price":345},{"products":[{"type":"Тесто","value":"Ржаное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Колбаса"},{"type":"Начинка","value":"Грибы"},{"type":"Начинка","value":"Томаты"}],"price":360},{"products":[{"type":"Тесто","value":"Ржаное тесто"},{"type":"Сыр","value":"Моцарелла"},{"type":"Сыр","value":"Рикотта"},{"type":"Начинка","value":"Ветчина"},{"type":"Начинка","value":"Грибы"},{"type":"Начинка","value":"Томаты"}],"price":365}]

