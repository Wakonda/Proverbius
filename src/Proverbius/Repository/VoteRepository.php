<?php

namespace Proverbius\Repository;

use Doctrine\DBAL\Connection;
use Proverbius\Entity\Vote;

/**
 * Vote repository
 */
class VoteRepository extends GenericRepository
{
	public function save($entity, $id = null)
	{
		// die(var_dump($entity->getUser()->getId()));
		$entityData = array(
        'vote'  => $entity->getVote(),
        'user_id' => ($entity->getUser()->getId() == null) ? null : $entity->getUser()->getId(),
        'proverb_id' => ($entity->getProverb()->getId() == 0) ? null : $entity->getProverb()->getId()
		);

		if(empty($id))
		{
			$this->db->insert('vote', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('vote', $entityData, array('id' => $id));

		return $id;
	}
	
	public function checkIfUserAlreadyVote($id, $idUser)
	{
		$data = $this->db->fetchAssoc('SELECT COUNT(*) AS votes_number FROM vote WHERE proverb_id = ? AND user_id = ?', array($id, $idUser));
		
		return $data['votes_number'];
	}
	
	public function countVoteByProverb($id, $vote)
	{
		$data = $this->db->fetchAssoc('SELECT COUNT(*) AS votes_number FROM vote WHERE proverb_id = ? AND vote = ?', array($id, $vote));
		
		return $data['votes_number'];
	}

	public function findVoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array('pf.text', 'vo.vote');
		
		$qb->select("pf.id, pf.text, vo.vote, pf.slug AS slug")
		   ->from("vote", "vo")
		   ->leftjoin("vo", "user", "bp", "vo.user_id = bp.id")
		   ->leftjoin("vo", "proverb", "pf", "vo.proverb_id = pf.id")
		   ->where("bp.username = :username")
		   ->setParameter("username", $username);
		   
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andhere('pf.text LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(*) AS count");
			return $qb->execute()->fetchColumn();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
	}

	public function build($data, $show = false)
    {
        $entity = new Vote();

        $entity->setId($data['id']);
        $entity->setVote($data['vote']);
		
		if($show)
		{
			$entity->setUser($this->findByTable($data['user_id'], 'user', 'username'));
			$entity->setProverb($this->findByTable($data['proverb_id'], 'proverb', 'text'));
		}
		else
		{
			$entity->setUser($data['user_id']);
			$entity->setProverb($data['proverb_id']);
		}

        return $entity;
    }
}