<?php

namespace Proverbius\Controller;

use Proverbius\Entity\Vote;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VoteController
{
	public function voteAction(Request $request, Application $app, $id)
	{
		$vote = $request->query->get('vote');
		
		$state = "";
		
		if(!empty($vote))
		{
			$user = $app['security.token_storage']->getToken()->getUser();
			
			if(is_object($user))
			{
				$vote = ($vote == "up") ? 1 : -1;

				$entity = new Vote();
				
				$entity->setVote($vote);
				$entity->setProverb($app['repository.proverb']->find($id));
				
				
				$userDb = $app['repository.user']->findByUsernameOrEmail($user->getUsername());
				$entity->setUser($userDb);
			
				$numberOfDoubloons = $app['repository.vote']->checkIfUserAlreadyVote($id, $userDb->getId());
				
				if($numberOfDoubloons >= 1)
					$state = "Vous avez déjà voté pour ce proverbe";
				else
					$app['repository.vote']->save($entity);
			}
			else
				$state = "Vous devez être connecté pour pouvoir voter !";
		}

		$up_values = $app['repository.vote']->countVoteByProverb($id, 1);
		$down_values = $app['repository.vote']->countVoteByProverb($id, -1);
		$total = $up_values + $down_values;
		$value = ($total == 0) ? 50 : round(((100 * $up_values) / $total), 1);

		$response = new Response(json_encode(array("up" => $up_values, "down" => $down_values, "value" => $value, "alreadyVoted" => $state)));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
}