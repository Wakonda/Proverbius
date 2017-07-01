<?php

namespace Proverbius\Controller;

use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Proverbius\Form\Type\IndexSearchType;
use Proverbius\Service\MailerPinturus;
use Proverbius\Service\Captcha;
use Proverbius\Service\Gravatar;

use MatthiasMullie\Minify;

require_once __DIR__.'/../../../src/html2pdf_v4.03/Html2Pdf.php';
require_once __DIR__.'/../../simple_html_dom.php';

class IndexController
{
    public function indexAction(Request $request, Application $app)
    {
		$form = $this->createForm($app, null);
		$random = $app['repository.proverb']->getRandomProverb();
		
        return $app['twig']->render('Index/index.html.twig', array('form' => $form->createView(), 'random' => $random));
    }
	
	public function indexSearchAction(Request $request, Application $app)
	{
		$search = $request->request->get("index_search");
		$criteria = array_filter(array_values($search));

		return $app['twig']->render('Index/resultIndexSearch.html.twig', array('search' => base64_encode(json_encode($search)), 'criteria' => $criteria));
	}

	public function indexSearchDatatablesAction(Request $request, Application $app, $search)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}
		$sSearch = json_decode(base64_decode($search));

		$entities = $app['repository.proverb']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.proverb']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $app['url_generator']->generate('read', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
			$country = $entity->getCountry();
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getText().'</a>';
			$row[] = '<img src="'.$request->getBaseUrl().'/photo/country/'.$country['flag'].'" class="flag">';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function readAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.proverb']->find($id, true);
		
		$browsingProverbs = $app['repository.proverb']->browsingProverbShow($id);

		return $app['twig']->render('Index/read.html.twig', array('entity' => $entity, 'browsingProverbs' => $browsingProverbs));
	}

	public function readPDFAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.proverb']->find($id, true);
		$content = $app['twig']->render('Index/pdf.html.twig', array('entity' => $entity));

		$html2pdf = new \HTML2PDF('P','A4','fr');
		$html2pdf->WriteHTML($content);
// die(($content));
		$file = $html2pdf->Output('proverb.pdf');
		$response = new Response($file);
		$response->headers->set('Content-Type', 'application/pdf');

		return $response;
	}

	public function lastAction(Request $request, Application $app)
    {
		$entities = $app['repository.proverb']->getLastEntries();

		return $app['twig']->render('Index/last.html.twig', array('entities' => $entities));
    }
	
	public function statAction(Request $request, Application $app)
    {
		$statistics = $app['repository.proverb']->getStat();

		return $app['twig']->render('Index/stat.html.twig', array('statistics' => $statistics));
    }

	// COUNTRY
	public function countryAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.country']->find($id, true);

		return $app['twig']->render('Index/country.html.twig', array('entity' => $entity));
	}

	public function countryDatatablesAction(Request $request, Application $app, $countryId)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();

		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.proverb']->getProverbByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId);
		$iTotal = $app['repository.proverb']->getProverbByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity["proverb_text"];
			$show = $app['url_generator']->generate('read', array('id' => $entity["proverb_id"], 'slug' => $entity["proverb_slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	// BY COUNTRIES
	public function byCountriesAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bycountry.html.twig');
    }
	
	public function byCountriesDatatablesAction(Request $request, Application $app)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.proverb']->findProverbByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.proverb']->findProverbByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['country_id']))
			{
				$row = array();

				$show = $app['url_generator']->generate('country', array('id' => $entity['country_id'], 'slug' => $entity['country_slug']));
				$row[] = '<a href="'.$show.'" alt="Show"><img src="'.$request->getBaseUrl().'/photo/country/'.$entity['flag'].'" class="flag" /> '.$entity['country_title'].'</a>';

				$row[] = $entity['number_proverbs_by_country'];

				$output['aaData'][] = $row;
			}
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	// BY LETTER
	public function letterAction(Request $request, Application $app, $letter)
	{
		return $app['twig']->render('Index/letter.html.twig', array('letter' => $letter));
	}

	public function letterDatatablesAction(Request $request, Application $app, $letter)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.proverb']->getProverbByLetterDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $letter);
		$iTotal = $app['repository.proverb']->getProverbByLetterDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $letter, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity["proverb_text"];
			$show = $app['url_generator']->generate('read', array('id' => $entity["proverb_id"], 'slug' => $entity["proverb_slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function byLettersAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/byletter.html.twig');
    }

	public function byLettersDatatablesAction(Request $request, Application $app)
	{
		$results = [];
		
		foreach(range('A', 'Z') as $letter)
		{
			$subArray = [];
			
			$subArray["letter"] = $letter;
			
			$resQuery = $app['repository.proverb']->findProverbByLetter($letter);
			$subArray["link"] = $resQuery["number_letter"];
			$results[] = $subArray;

		}
		
		return $app['twig']->render('Index/byletterDatatable.html.twig', array('results' => $results));
	}

	public function reloadCaptchaAction(Request $request, Application $app)
	{
		$captcha = new Captcha($app);

		$wordOrNumberRand = rand(1, 2);
		$length = rand(3, 7);

		if($wordOrNumberRand == 1)
			$word = $captcha->wordRandom($length);
		else
			$word = $captcha->numberRandom($length);

		$response = new Response(json_encode(array("new_captcha" => $captcha->generate($word))));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function reloadGravatarAction(Request $request, Application $app)
	{
		$gr = new Gravatar();

		$response = new Response(json_encode(array("new_gravatar" => $gr->getURLGravatar())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function pageAction(Request $request, Application $app, $name)
	{
		$entity = $app['repository.page']->findByName($name);
		
		return $app['twig']->render('Index/page.html.twig', array("entity" => $entity));
	}

	private function createForm($app, $entity)
	{
		$countryForms = $app['repository.country']->findAllForChoice();

		$form = $app['form.factory']->create(IndexSearchType::class, null, array("countries" => $countryForms));
		
		return $form;
	}
}