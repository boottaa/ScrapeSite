<?php
/**
 * Created by PhpStorm.
 * User: bootta
 * Date: 29.11.17
 * Time: 15:09
 */

namespace Users\Scrape;


use Zend\Dom\Query;
use Zend\Http\Client;

class ScrapeLifemebel
{
	const HOST = "lifemebel.ru";
	const PROTOCOL = "http://";
	private $nextPage = "";
	private $currentPage = "";

	private $next = 0;




	public function scrapeLifemebel($menuNum = 0, $limit = 5)
	{
		$data = [];

		Scrape:

		$this->nextPage();
		$result = $this->nextPageRequest();
		if($result){
			$html = $result->getBody();
			$goods = $this->getGoods($html);
//			if(!empty($goods))
//			{
				$data[$this->currentPage] = $goods;
//			}
			sleep(2);
			if($menuNum < $limit){
				$menuNum++;
				goto Scrape;
			}else{
				return	$data;
			}
		}else{
			return	$data;
		}
	}

	public function getMenu()
	{
		$response = $this->request();
		$html = $response->getBody();

		$dom = new Query($html);
		$results = $dom->execute("#site_head_menu>li a");

		$menu = [];
		foreach ($results as $result) {
			if(preg_match("/^\/.*/",$result->getAttribute("href")))
			{
				$menu[] = $result->getAttribute("href");
			}
		}

		return $menu;
	}

	private function getGoods($html)
	{
		$dom = new Query($html);
		$results = $dom->execute(".catalog_list_el.horizontal");

		/**
		 * name - Название товара
		 * price - Цена товара
		 * category - Категория товара
		 * id_external - Внешней ID если есть
		 * host - Хост с которого был получен товар
		 * url - на товар в магазине
		 */

		$data = [];
		if(count($results) >= 1)
		{
			foreach ($results as $k => $result)
			{
				$xml = simplexml_import_dom($result);
				$subd = new Query($xml->asXml());

				if(empty($result->getAttribute('data-name')))
				{
					break;
				}
				$data[$k] = [
					"name" => $result->getAttribute('data-name'),
					"price" => $result->getAttribute('data-price'),
					"category" => $result->getAttribute('data-category'),
					"id_external" => $result->getAttribute('data-gtm-id'),
					"host" => self::HOST,
				];

				foreach ($subd->execute("a.photo>img.main_photo") as $photo)
				{
					$data[$k]['img'] = $photo->getAttribute("src");
				}
				foreach ($subd->execute("div.name>a") as $url)
				{
					$data[$k]['url'] = $url->getAttribute("href");
				}
			}
		}

		return $data;
	}

	private function nextPage()
	{
		$client = new Client();

		if(!$this->nextPage){
			$url = ($this->getMenu())[$this->next];
			$this->currentPage = $url;

			$this->next++;
		}else{
			$this->currentPage = $this->nextPage;
		}
		$html = $this->request($this->currentPage)->getBody();
		$page = $this->pagination($html);
		if($page)
		{
			$this->nextPage = $page;
		}else{
			$this->nextPage = false;
		}
	}

	private function pagination($html)
	{
		$dom = new Query($html);
		$results = $dom->execute(".sort_block .navigation_wrapper .navigation a");

		$page = [];
		foreach ($results as $result) {
			$page[] = $result->getAttribute("href");
		}


		if(!empty(end($page)))
		{
			return end($page);
		}else{
			return false;
		}
	}

	private function request($url = '')
	{
		$client = new Client();

		$client->setUri('http://'.self::HOST.$url);
		$client->setOptions([
			'maxredirects' => 5,
			'timeout'      => 30,
		]);

		return $client->send();
	}

	private function nextPageRequest()
	{
		if(empty($this->currentPage)) {
			return false;
		}
		$client = new Client();
		$client->setUri('http://' . self::HOST . $this->currentPage);
		$client->setOptions([
			'maxredirects' => 5,
			'timeout' => 30,
		]);

		return $client->send();

	}


}