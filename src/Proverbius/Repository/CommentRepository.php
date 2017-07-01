<?php

namespace Proverbius\Repository;

use Doctrine\DBAL\Connection;
use Proverbius\Entity\Comment;

/**
 * Comment repository
 */
class CommentRepository extends GenericRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
        'text'  => $entity->getText(),
        'created_at' => $entity->getCreatedAt(),
        'proverb_id' => ($entity->getProverb() == 0) ? null : $entity->getProverb(),
        'user_id' => ($entity->getUser()->getId() == null) ? null : $entity->getUser()->getId(),
		);

		if(empty($id))
		{
			$this->db->insert('comment', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('comment', $entityData, array('id' => $id));

		return $id;
	}

	public function findAll()
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("*")
		   ->from("comment", "pf");

		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}

	public function build($data, $show = false)
    {
        $entity = new Comment();

        $entity->setId($data['id']);
        $entity->setText($data['text']);
        $entity->setCreatedAt(new \Datetime($data['created_at']));
		
		if($show)
		{
			$entity->setProverb($this->findByTable($data['proverb_id'], 'proverb'));
			
			$entity->setUser($this->findByTable($data['user_id'], 'user', 'username'));
		}
		else
		{
			$entity->setProverb($data['proverb_id']);
			$entity->setUser($data['user_id']);
		}

        return $entity;
    }

	public function countAllComments($id)
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("COUNT(*) AS count")
		   ->from('comment', 'c')
		   ->where('c.proverb_id = :id')
		   ->setParameter('id', $id);

		return $qb->execute()->fetchColumn();
	}
	
	public function displayComments($id, $max_comment_by_page, $first_message_to_display)
	{
		$qb = $this->db->createQueryBuilder();
		
		$first_message_to_display = ($first_message_to_display < 0) ? 0 : $first_message_to_display;

		$qb->select("*")
		   ->from("comment", "c")
		   ->where('c.proverb_id = :id')
		   ->setParameter('id', $id)
		   ->setMaxResults($max_comment_by_page)
		   ->setFirstResult($first_message_to_display)
		   ->orderBy("c.created_at", "DESC");

		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}

	public function findCommentByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array('pf.text', 'co.created_at');
		
		$qb->select("pf.id, pf.text, co.created_at, pf.slug AS slug")
		   ->from("comment", "co")
		   ->leftjoin("co", "user", "bp", "co.user_id = bp.id")
		   ->leftjoin("co", "proverb", "pf", "co.proverb_id = pf.id")
		   ->where("bp.username = :username")
		   ->setParameter("username", $username)
		   ->orderBy("co.created_at", "DESC");
		   
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andhere('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(*) AS count");
			return $qb->execute()->fetchColumn();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->execute()->fetchAll();
	}
}