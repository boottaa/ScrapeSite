<?php
namespace Users\Controller;

use Fabrikant\Tool\XmlReader;
use Users\Scrape\ScrapeLifemebel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\A as AuthAdapter;
use Zend\Config\Reader\Xml;
use Zend\Dom\Query;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\Config;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter as dbAdapter;
use Zend\Http\Client;

class UsersController extends AbstractActionController
{

	private $dbAdapter;

	public function __construct($db)
	{
		/** @var  dbAdapter */
		$this->dbAdapter = $db;
	}

	public function indexAction()
	{
		$x = new ScrapeLifemebel();

		echo "<pre>";
		print_r($x->getMenu());
		echo "<h1>GOODS</h1>";
		print_r($x->scrapeLifemebel(0, 40));
		die();

//		$client = new Client();
//		$host = "lifemebel.ru";
//		$client->setUri('http://'.$host.$this->getMenu()[2]);
//		$client->setOptions([
//			'maxredirects' => 5,
//			'timeout'      => 30,
//		]);
//
//		$response = $client->send();
//		$html = $response->getBody();
//
//
//		$dom = new Query($html);
//		$results = $dom->execute(".sort_block .navigation_wrapper .navigation a");
//
//		$page = [];
//		foreach ($results as $result) {
//			$page[] = $result->getAttribute("href");
//		}
//		$nextPage = end($page);
//
//		//$goods = $this->getGoods($html, $host);
//
//
//		echo "<pre>";
//		print_r($nextPage);
//		die();

	}








}

?>



