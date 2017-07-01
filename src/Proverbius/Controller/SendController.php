<?php

namespace Proverbius\Controller;

use Silex\Application;
use Proverbius\Entity\Contact;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Proverbius\Form\Type\SendType;
use Proverbius\Service\Mailer;

class SendController
{
    public function indexAction(Request $request, Application $app, $id)
    {
		$form = $app['form.factory']->create(SendType::class, null);
		
		$app['locale'] = $request->getLocale();

        return $app['twig']->render('Index/send.html.twig', array('form' => $form->createView(), 'id' => $id));
    }
	
	public function sendAction(Request $request, Application $app, $id)
	{
		parse_str($request->request->get('form'), $form_array);

        $form = $app['form.factory']->create(SendType::class, $form_array);
		
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$data = (object)($request->request->get($form->getName()));
			$entity = $app['repository.proverb']->find($id, true);
		
			$content = $app['twig']->render('Index/send_message_content.html.twig', array(
				"data" => $data,
				"entity" => $entity
			));

			$mailer = new Mailer($app['swiftmailer.options']);
			
			$mailer->setSubject($data->subject);
			$mailer->setSendTo($data->recipientMail);
			$mailer->setBody($content);
			
			$mailer->send();
			
			$response = new Response(json_encode(array("result" => "ok")));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		}

		$res = array("result" => "error");
		
		$res["content"] = $app['twig']->render('Index/send_form.html.twig', array('form' => $form->createView(), 'id' => $id));
		
		$response = new Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
}