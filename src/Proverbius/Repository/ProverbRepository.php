<?php

namespace Proverbius\Repository;

use Doctrine\DBAL\Connection;
use Proverbius\Entity\Proverb;

/**
 * Proverb repository
 */
class ProverbRepository extends GenericRepository implements iRepository
{
	public function save($entity, $id = null)
	{
		if(empty($entity->getSlug()))
			$entity->setSlug($entity->getText());
		
		$entityData = array(
			'text' => $entity->getText(),
			'country_id' => ($entity->getCountry() == 0) ? null : $entity->getCountry(),
			'slug' => $entity->getSlug()
		);

		if(empty($id))
		{
			$this->db->insert('proverb', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('proverb', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM proverb WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }

	public function findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $datasObject, $count = false)
	{
		$aColumns = array( 'pa.text', 'co.title');
		$qb = $this->db->createQueryBuilder();

		$qb->select("pa.*")
		   ->from("proverb", "pa")
		   ->leftjoin("pa", "country", "co", "pa.country_id = co.id");

		if(!empty($datasObject->text))
		{
			$keywords = explode(",", $datasObject->text);
			$i = 0;
			foreach($keywords as $keyword)
			{
				$keyword = "%".$keyword."%";
				$qb->andWhere("(pa.text LIKE :keyword".$i)
				   ->orWhere("pa.text LIKE :keywordEntities".$i.")")
			       ->setParameter("keyword".$i, $keyword)
			       ->setParameter("keywordEntities".$i, htmlentities($keyword));
				$i++;
			}
		}

		if(!empty($datasObject->country))
		{
			$qb->andWhere("co.country_id = :country")
			   ->setParameter("country", $datasObject->country);
		}

		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if($count)
		{
			$qb->select("COUNT(*) AS count");
			return $qb->execute()->fetchColumn();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}

    public function findProverbByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'co.title', 'COUNT(pa.id)');
		
		$qb->select("co.id AS country_id, co.title AS country_title, COUNT(pa.id) AS number_proverbs_by_country, co.flag AS flag, co.slug AS country_slug")
		   ->from("proverb", "pa")
		   ->leftjoin("pa", "country", "co", "pa.country_id = co.id")
		   ->groupBy("co.id, co.title, co.flag")
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->fetchAssoc("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			return $countRows['count'];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }

    public function findProverbByLetter($letter, $count = false)
    {
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("COUNT(pa.id) AS number_letter")
		   ->from("proverb", "pa")
		   ->where("SUBSTRING(pa.text, 1, 1) = :letter")
		   ->setParameter("letter", $letter);

		$dataArray = $qb->execute()->fetch();

		return $dataArray;
    }

	public function getRandomProverb()
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS countRow")
		   ->from("proverb", "pt");
		
		$max = $qb->execute()->fetchColumn() - 1;
		$offset = rand(0, $max);

		$qb = $this->db->createQueryBuilder();

		$qb->select("*")
		   ->from("proverb", "pt")
		   ->setFirstResult($offset)
		   ->setMaxResults(1);

		$result = $qb->execute()->fetch();
		
		if(!$result)
			return null;

		return $this->build($result, true);
	}

	public function getLastEntries()
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("*")
		   ->from("proverb", "pt")
		   ->setMaxResults(7)
		   ->orderBy("pt.id", "DESC");
		   
		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}
	
	public function getStat()
	{
		$qbProverb = $this->db->createQueryBuilder();

		$qbProverb->select("COUNT(*) AS count_proverb")
			   ->from("proverb", "pt");
		
		$resultProverb = $qbProverb->execute()->fetchColumn();
		
		return array("count_proverb" => $resultProverb);
	}

	public function build($data, $show = false)
    {
        $entity = new Proverb();

        $entity->setId($data['id']);
        $entity->setText($data['text']);
		$entity->setSlug($data['slug']);
		
		if($show)
		{
			$entity->setCountry($this->findByTable($data['country_id'], 'country'));
		}
		else
		{
			$entity->setCountry($data['country_id']);
		}

        return $entity;
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.text', 'pf.id');
		
		$qb->select("*")
		   ->from("proverb", "pf");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pf.text LIKE :search')
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
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data);
        }
			
		return $entitiesArray;
	}

	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS count")
		   ->from("proverb", "pf")
		   ->where("pf.text = :text")
		   ->setParameter('text', $entity->getText());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}

		$results = $qb->execute()->fetchAll();
		return $results[0]["count"];
	}

	public function browsingProverbShow($ProverbId)
	{
		// Previous
		$subqueryPrevious = 'p.id = (SELECT MAX(p2.id) FROM proverb p2 WHERE p2.id < '.$ProverbId.')';
		$qb_previous = $this->db->createQueryBuilder();
		
		$qb_previous->select("p.id, p.text, p.slug AS slug")
		   ->from("proverb", "p")
		   ->andWhere($subqueryPrevious);
		   
		// Next
		$subqueryNext = 'p.id = (SELECT MIN(p2.id) FROM proverb p2 WHERE p2.id > '.$ProverbId.')';
		$qb_next = $this->db->createQueryBuilder();
		
		$qb_next->select("p.id, p.text, p.slug AS slug")
		   ->from("proverb", "p")
		   ->andWhere($subqueryNext);
		
		$res = array(
			"previous" => $qb_previous->execute()->fetch(),
			"next" => $qb_next->execute()->fetch()
		);

		return $res;
	}

	public function getProverbByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array('pf.text', 'pf.id');
		
		$qb->select("pf.text AS proverb_text, pf.id AS proverb_id, pf.slug AS proverb_slug")
		   ->from("proverb", "pf")
		   ->innerjoin("pf", "country", "co", "pf.country_id = co.id")
		   ->where("pf.country_id = :id")
		   ->setParameter("id", $countryId);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.text LIKE :search')
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

	public function getProverbByLetterDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $letter, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array('pf.text', 'pf.id');
		
		$qb->select("pf.text AS proverb_text, pf.id AS proverb_id, pf.slug AS proverb_slug")
		   ->from("proverb", "pf")
		   ->where("SUBSTRING(pf.text, 1, 1) = :letter")
		   ->setParameter("letter", $letter);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.text LIKE :search')
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
}