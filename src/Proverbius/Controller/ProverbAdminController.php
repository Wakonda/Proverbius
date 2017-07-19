<?php

namespace Proverbius\Controller;

use Proverbius\Entity\Proverb;
use Proverbius\Form\Type\ProverbType;
use Proverbius\Form\Type\ProverbFastMultipleType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../../simple_html_dom.php';

class ProverbAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Proverb/index.html.twig');
	}

	public function indexDatatablesAction(Request $request, Application $app)
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
		
		$entities = $app['repository.proverb']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.proverb']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity->getId();
			$row[] = $entity->getText();
			
			$show = $app['url_generator']->generate('proverbadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('proverbadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a> - <a href="'.$edit.'" alt="Edit">Modifier</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Proverb();
        $form = $this->createForm($app, $entity);

		return $app['twig']->render('Proverb/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Proverb();
        $form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);

		if($form->isValid())
		{
			$id = $app['repository.proverb']->save($entity);

			$redirect = $app['url_generator']->generate('proverbadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Proverb/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.proverb']->find($id, true);
	
		return $app['twig']->render('Proverb/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.proverb']->find($id);
		$form = $this->createForm($app, $entity);
	
		return $app['twig']->render('Proverb/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.proverb']->find($id);
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if($form->isValid())
		{
			$id = $app['repository.proverb']->save($entity, $id);

			$redirect = $app['url_generator']->generate('proverbadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Proverb/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function deleteAction(Request $request, Application $app, $id)
	{
		$app['repository.proverb']->delete($id);
		$app['session']->getFlashBag()->add('message', 'Le proverbe a été supprimé avec succès !');

		return $app->redirect($app['url_generator']->generate('proverbadmin_index'));
	}

	public function newFastMultipleAction(Request $request, Application $app)
	{
		$countries = $app['repository.country']->findAllForChoice();

		$form = $app['form.factory']->create(ProverbFastMultipleType::class, null, array('countries' => $countries));

		return $app['twig']->render('Proverb/fastMultiple.html.twig', array('form' => $form->createView()));
	}
	
	public function addFastMultipleAction(Request $request, Application $app)
	{
		$entity = new Proverb();
		$countries = $app['repository.country']->findAllForChoice();
		
		$form = $app['form.factory']->create(ProverbFastMultipleType::class, $entity, array('countries' => $countries));
		
		$form->handleRequest($request);
		$req = $request->request->get($form->getName());

		if(!empty($req["url"]) and filter_var($req["url"], FILTER_VALIDATE_URL))
		{
			$url = $req["url"];
			$url_array = parse_url($url);
			
			$authorizedURLs = ['d3d3LmxpbnRlcm5hdXRlLmNvbQ==', 'Y2l0YXRpb24tY2VsZWJyZS5sZXBhcmlzaWVuLmZy', 'ZGljb2NpdGF0aW9ucy5sZW1vbmRlLmZy'];
			
			if(!in_array(base64_encode($url_array['host']), $authorizedURLs))
				$form->get("url")->addError(new FormError('URL inconnue'));
		}

		if($form->isValid())
		{
			if(!empty($ipProxy = $form->get('ipProxy')->getData()))
				$html = str_get_html($app['generic_function']->file_get_contents_proxy($url, $ipProxy));
			else
				$html = file_get_html($url, false, null, 0);

			$proverbsArray = [];
			
			switch(base64_encode($url_array['host']))
			{
				case 'd3d3LmxpbnRlcm5hdXRlLmNvbQ==':
					foreach($html->find('td.libelle_proverbe strong') as $pb)
					{					
						$entityProverb = clone $entity;
						$text = str_replace("\r\n", "", trim($pb->plaintext));
						
						$entityProverb->setText($text);

						$proverbsArray[] = $entityProverb;
					}
					break;
				case 'Y2l0YXRpb24tY2VsZWJyZS5sZXBhcmlzaWVuLmZy':
					foreach($html->find('div#citation_citationSearchList q') as $pb)
					{					
						$entityProverb = clone $entity;
						$text = str_replace("\r\n", "", trim($pb->plaintext));
						
						$entityProverb->setText($text);
						
						$proverbsArray[] = $entityProverb;
					}
					break;
				case 'ZGljb2NpdGF0aW9ucy5sZW1vbmRlLmZy':
					foreach($html->find('div#content blockquote') as $pb)
					{
						$entityProverb = clone $entity;
						$text = str_replace("\r\n", "", trim(utf8_encode($pb->plaintext)));
						
						$entityProverb->setText($text);
						
						$proverbsArray[] = $entityProverb;
					}
					break;
			}
			
			$numberAdded = 0;
			$numberDoubloons = 0;
			
			
			foreach($proverbsArray as $proverb)
			{
				if($app['repository.proverb']->checkForDoubloon($proverb) > 0)
					$numberDoubloons++;
				else
				{
					$app['repository.proverb']->save($proverb);
					$numberAdded++;
				}
			}

			$app['session']->getFlashBag()->add('message', $numberAdded.' proverbe(s) ajouté(s), '.$numberDoubloons.' doublon(s)');
	
			return $app->redirect($app['url_generator']->generate('proverbadmin_index'));
		}
		
		return $app['twig']->render('Proverb/fastMultiple.html.twig', array('form' => $form->createView()));
	}
	
	private function createForm($app, $entity)
	{
		$countryForms = $app['repository.country']->findAllForChoice();
		
		$form = $app['form.factory']->create(ProverbType::class, $entity, array("countries" => $countryForms));
		
		return $form;
	}
	
	private function checkForDoubloon($entity, $form, $app)
	{
		if($entity->getText() != null)
		{
			$checkForDoubloon = $app['repository.proverb']->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("text")->addError(new FormError('Cette entrée existe déjà !'));
		}
	}
}