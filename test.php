<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die(); // если файлы не проинклудились, то выходим.
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
use Bitrix\Main; // инфо для компилятора. какой класс выполнять

$defaultParams = array( // стандартные параметры
	'TEMPLATE_THEME' => 'blue' // графическая схема синяя
);
$arParams = array_merge($defaultParams, $arParams); // склеивание двух массивов
unset($defaultParams); //удаление переменной
$arParams['TEMPLATE_THEME'] = (string)($arParams['TEMPLATE_THEME']); // приведение в строку
if ('' != $arParams['TEMPLATE_THEME']) // проверка на пустую строку
{
	$arParams['TEMPLATE_THEME'] = preg_replace('/[^a-zA-Z0-9_\-\(\)\!]/', '', $arParams['TEMPLATE_THEME']); // удаление лишних символов
	if ('site' == $arParams['TEMPLATE_THEME']) // сравнивание параметра со строкой
	{
		$templateId = (string)Main\Config\Option::get('main', 'wizard_template_id', 'eshop_bootstrap', SITE_ID); // передаем параметры
		$templateId = (preg_match("/^eshop_adapt/", $templateId)) ? 'eshop_adapt' : $templateId; // проверка на соответствие
		$arParams['TEMPLATE_THEME'] = (string)Main\Config\Option::get('main', 'wizard_'.$templateId.'_theme_id', 'blue', SITE_ID); // передача параметров
	}
	if ('' != $arParams['TEMPLATE_THEME']) // если не пустой
	{
		if (!is_file($_SERVER['DOCUMENT_ROOT'].$this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css')) // выбор нужного стиля
			$arParams['TEMPLATE_THEME'] = ''; // обнуление
	}
}
if ('' == $arParams['TEMPLATE_THEME']) // если пустой
	$arParams['TEMPLATE_THEME'] = 'blue'; // задаем синий

use Bitrix\Highloadblock as HL; // использование классов
use Bitrix\Main\Entity; // использование классов
CModule::IncludeModule('highloadblock'); // подключение модуля
$be = new CIBlockElement(); // создание нового объекта
$cFile = new CFile(); // создание нового объекта
$colorXmlIds = array(); // создание нового массива
$colors = array(); // создание нового массива
$colorElems = array(); // создание нового массива
$colorElemsIds = array(); // создание нового массива
$brands = array();// создание нового массива
$brandsName = array(); // создание нового массива
$brandProp = false; // обознае переменной
$prodsCount = 0; // обознае переменной
$images = array(); // создание нового массива
$dbBrandProp = CIBlockProperty::GetList( // создание списка, каталога. брэнд и его айди
	array(),
	array(
		'IBLOCK_ID' => Config::$iblock['catalog'],
		'CODE' => 'BRAND'
	)
);
if ($dbBrandProp->SelectedRowsCount() > 0) // если выбранных записей больше нуля
{
	$brandProp = $dbBrandProp->Fetch(); // смотрим список
	$brandProp = $brandProp['ID']; // получаем айди бренда
}
foreach ($arResult['GRID']['ROWS'] as $key => $row) { // цикл перебора по ключу
	$arResult['GRID']['ROWS'][$key]["SUM"] = round(str_replace(" ", "", $arResult['GRID']['ROWS'][$key]["SUM"]));
	$arResult['GRID']['ROWS'][$key]["PRICE_FORMATED"] = round(str_replace(" ", "", $arResult['GRID']['ROWS'][$key]["PRICE_FORMATED"]));
	if (!empty($row['SKU_DATA'])) // если не пустое значение
	{
		if (!empty($brandProp) && !empty($row['CATALOG']['PROPERTY_' . $brandProp . '_VALUE'])) // если выбраны сортировки?
		{
			$brands[$row['ID']] = $row['CATALOG']['PROPERTY_' . $brandProp . '_VALUE'][0]; // присвоение айди строки первого значения
		}

		if (!empty($row['CATALOG']['PROPERTIES']['COLOR_ELEM']['VALUE'])) // если выбран каталог, пропорции, цвет эллемента, заголовок
		{
			$colorElems[$row['ID']] = $row['CATALOG']['PROPERTIES']['COLOR_ELEM']['VALUE']; // цвет выбранного элемента равен заданным параметрам
		}
		foreach ($row['SKU_DATA'] as $prop) // цикл перебора данных
		{
			if ($prop['CODE'] != 'COLOR') // если код не равен нужному цвету
			{
				continue; // пропуск
			}
			foreach ($prop['VALUES'] as $value) // цикл перебора заголовков
			{
				$colorXmlIds[] = $value['XML_ID']; // присвоение значения
			}
		}
		unset($prop, $value); // разрушение переменных
	}
}
unset($row); // разрушение переменной
$colorXmlIds = array_unique($colorXmlIds); // присвоение переменной
$items = array_unique($items); // присвоение переменной
$colorElemsIds = array_unique(array_values($colorElems)); // присвоение переменной
if (!empty($colorElemsIds)) // если переменная не пустая
{
	$dbImages = $be->GetList( // создаем лист
		array(),
		array(
			'IBLOCK_ID' => Config::$iblock['element_color'],
			'ID' => $colorElemsIds
		),
		false,
		false,
		array('ID', 'DETAIL_PICTURE')
	);

	while ($image = $dbImages->Fetch()) // перебираем данные по изображениям из базы
	{
		if (empty($image['DETAIL_PICTURE'])) { // если ячейка пустая
			$image['DETAIL_PICTURE'] = Config::$img['no_img']; // возможно подставляется стандартная
		}

		$images[$image['ID']] = $cFile->ResizeImageGet(  // уменьшение картинки
			$image['DETAIL_PICTURE'],
			array('width' => 120, 'heigth' => 150)
		);
	}
}
if (!empty($colorXmlIds)) // если переменная не пуста
{
	$hlColorClass = IncFunc::getHlblockClass(Config::$hlblock['colors']); // присвоение переменной функции
	$rsDataColor = $hlColorClass::getList(array( // создание листа
		'order' => array('ID' => 'ASC'),
		'filter' => array(
			'UF_XML_ID' => $colorXmlIds
		),
		'select' => array(
			'XML_ID' => 'UF_XML_ID',
			'VALUE' => 'UF_NAME',
			'HEX' => 'UF_HEX',
			'HEX_BORDER' => 'UF_HEX_BORDER'
		)
	));
	while ($color = $rsDataColor->fetch()) // перебираем данные по цветам из базы
	{
		$color['HEX'] = is_null($color['HEX']) ? 'fff' : $color['HEX']; // тернарное выражение для цвета
		$color['HEX_BORDER'] = is_null($color['HEX_BORDER']) ? '000' : $color['HEX_BORDER']; // тернарное выражение для бордера
		$colors[$color['VALUE']] = $color; // присвоение цвета заголовка
	}
	unset($color); // очистка переменной
}
if (!empty($brands)) // если выбран бренд
{
	$hlBrandClass = IncFunc::getHlblockClass(Config::$hlblock['brands']); // подключение функции/класса
	$rsDataBrand = $hlBrandClass::getList(array( // создание списка
		'order' => array('ID' => 'ASC'),
		'filter' => array(
			'UF_XML_ID' => $brands
		),
		'select' => array(
			'XML_ID' => 'UF_XML_ID',
			'NAME' => 'UF_NAME',
		)
	));
	while ($brand = $rsDataBrand->fetch()) // перебор брендов из бд
	{
		$brandsName[$brand['XML_ID']] = $brand['NAME']; // присвоение названия по айди
	}
}
foreach ($arResult['GRID']['ROWS'] as &$row) // цикл перебора
{
	if (is_array($row)) { // если массив
		foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $key => $value) { // береборка эллементов
			if ($row["PRODUCT_ID"] == $value["PRODUCT_ID"]) { // если ячейка равна значению
				$row["PRODUCT_REAL_NAME"] = $value["CATALOG"]["PARENT_NAME"]; // присвоение полного названия
				$row["NEW_NAME"] = $value["CATALOG"]["PROPERTY_74_VALUE"][0]; // присвоение полного названия
				$row["FULL_PRICE"] = $value["FULL_PRICE"] * $row['QUANTITY']; // изменение цены
				break; // прерывание
			}
		}
	}
	$row['URLS'] = array( // список кнопок, удалить, отложить, добавить
		'DELETE' => $curPage.'?BasketRefresh=Y&action=delete&id=' . $row['ID'],
		'DELAY' => $curPage.'?BasketRefresh=Y&action=delay&id=' . $row['ID'],
		'ADD' => $curPage.'?BasketRefresh=Y&action=add&id=' . $row['ID'],
	);
	if ($row['CAN_BUY'] == 'Y' && $row['DELAY'] != 'Y' && $row['QUANTITY'] > 0) // если выбрана покупка
	{
		$prodsCount += $row['QUANTITY']; // прибавить к счетчику
	}
	$row['DETAIL_PICTURE'] = $images[$colorElems[$row['ID']]]['src']; // работа с изображением товара
	if (!empty($brandsName[$brands[$row['ID']]])) // если не пустое название бренда
	{

		$row['BRAND'] = $brandsName[$brands[$row['ID']]]; // тогда присвоить его переменной
	}
	$formatedProps = array(); // новый массив
	if (!empty($row['PROPS'])) // если не пусты пропорции
	{
		foreach ($row['PROPS'] as $prop) // переборка списка
		{
			$formatedProps[$prop['CODE']] = array(
				'CODE' => $prop['CODE'],
				'NAME' => $prop['NAME'],
				'VALUES' => array(),
				'CURRENT' => array(),
			);
			if ($prop['CODE'] == 'COLOR') // сравнение перменных
			{
				$formatedProps[$prop['CODE']]['CURRENT'] = $colors[$prop['VALUE']]; // присвоение переменной
				continue; // пропуск
			}
			$formatedProps[$prop['CODE']]['CURRENT'] = array( // присвоение массива
				'VALUE' => $prop['VALUE']
			);
		}
		unset($prop); // уничтожение
	}
	if (!empty($row['SKU_DATA'])) // если не пустое значение
	{
		foreach ($row['SKU_DATA'] as $prop) // цикл перебора
		{
			foreach ($prop['VALUES'] as $value) // цикл перебора
			{
				if ($prop['CODE'] == 'COLOR') // сравнение кода цвета
				{
					$formatedProps[$prop['CODE']]['VALUES'][] = $colors[$value['NAME']]; // присвоение цвета
					continue; // пропуск
				}
				$formatedProps[$prop['CODE']]['VALUES'][] = array( // массив имен
					'VALUE' => $value['NAME']
				);
			}
		}
		unset($prop, $value); // разрушение переменных
	}
	$row['PROPS_FORMATED'] = $formatedProps; // присвоение
}
unset($row); //разрушение
$arResult['prodsCount'] = $prodsCount; // счетчик
$arResult['SESSID'] = bitrix_sessid(); // айди сессии
if ($USER->IsAuthorized()){ // если пользователь авторизован
	$source_address = $USER->GetEmail(); // адрес ресурса - почта юзера
	$arResult['processed_address'] = strtolower($source_address); // нижний регистр
	$arResult['processed_address'] = trim($arResult['processed_address']); // удаление пробелов
	$arResult['processed_address'] = md5($arResult['processed_address']); // кодирование md5
} else { // иначе
	$arResult['processed_address'] = ""; // адрес пуст
$triggerProduct = 0; // бозначение переменной
$accessibleStores = ["склад-1", "склад-2", "склад-3", "склад-4", "склад-5", "склад-6", "склад-7", "склад-8", "склад-9"]; // массив складов
	foreach ($arResult["ITEMS"]["AnDelCanBuy"] as $key => $value) { // переборка корзины?
		$rsStore = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' =>$value["PRODUCT_ID"]), false, false, array('*'));
		while ($arStore = $rsStore->Fetch()) // переборка
		{
			if (in_array($arStore["STORE_NAME"], $accessibleStores) && $arStore["AMOUNT"] == 0) {
				$triggerProduct++; // счетчик +1
			}
		}
		if ($triggerProduct == count($accessibleStores) + 1) {
			if (CSaleBasket::Delete($value["ID"])) { // удаление эллемента
				$arResult["REFRESH"] = true;
			}
			$triggerProduct = 0; // присвоение
		} else {
			$triggerProduct = 0; // присвоение
		}
	}
	$arResult['allSum_FORMATED'] = round(str_replace(" ", "", $arResult['allSum_FORMATED'])); // чистка пробелов

?>
