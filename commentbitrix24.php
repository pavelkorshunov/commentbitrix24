<?php
// Запись комментария в заказе, для синхронизации с битрикс24
use Bitrix\Main;
use Bitrix\Sale;
Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderBeforeSaved',
    'OnSaleComponentHandler'
);

function OnSaleComponentHandler(Main\Event $event)
{

	$order = $event->getParameter("ENTITY");
	$commentbitrix24 = "";

	// Получаем адрес для записи в комментарий заказа
	$propertyCollection = $order->getPropertyCollection();
	
	$propertys = $propertyCollection->getArray();
	$address = "Адрес:";

	foreach ($propertys["properties"] as $location)
	{

		if($location["CODE"] === "ADDRESS")
		{
			$address .= $location["VALUE"][0] . "<br>";
		}
		elseif($location["CODE"] === "STREET" || $location["CODE"] === "HOUSE")
		{
			$address .= $location["VALUE"][0] . " ";
		}
	}
	$commentbitrix24 = $address;


	// Получаем артикулы обложек для записи в комментарий заказа
	$articles = "";
	$dbBasketItems = CSaleBasket::GetList(array(), array("ORDER_ID"=>$order->getId()));

	while ($arItems = $dbBasketItems->Fetch())
	{
		$db_props = CSaleBasket::GetPropsList(
		    array(),
		    array(
		    		"BASKET_ID"=>$arItems["ID"],
		    		"CODE"=>"FABRIC"
		        )
		);

		while ($item = $db_props->Fetch())
		{
			$articles = "<br/>Артикулы из обложки: " . $item["VALUE"];
		}
	}

	$commentbitrix24 .= $articles;
	// $str = print_r($commentbitrix24, true) . " \n";
	// file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/log_user.txt", $str, FILE_APPEND);

	$order->setField("COMMENTS", $commentbitrix24);
}
