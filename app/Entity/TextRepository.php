<?php namespace App\Entity;

use Doctrine\ORM\NoResultException;

class TextRepository extends EntityRepository {
	public static $types = [
		'anecdote',
		'fable',
		'biography',
		'dialogue',
		'docu',
		'essay',
		'interview',
		'gamebook',
		'memo',
		'science',
		'popscience',
		'novelette',
		'ocherk',
		'shortstory',
		'review',
		'novel',
		#'parable',
		'play',
		'letter',
		'poetry',
		'poem',
		'novella',
		'outro',
		'intro',
		'tale',
		'pritcha',
		'travelnotes',
		'speech',
		'article',
		'prosepoetry',
		'screenplay',
		'textbook',
		'feuilleton',
		'haiku',
		'jure',
		'critique',
		'philosophy',
		'religion',
		'historiography',
		'collection',
		'other',
	];

	protected $queryableFields = [
		'id',
		'title',
		'subtitle',
		'origTitle',
		'origSubtitle',
	];

	protected $sortableFields = [
		'commentCount',
		'rating',
		'title',
		'votes',
	];
	protected $defaultSortingField = 'title';

	/**
	 * @param int $id
	 * @param bool $fetchRelations
	 * @return Text
	 */
	public function get($id, $fetchRelations = true) {
		if (!$fetchRelations) {
			return $this->find($id);
		}
		return $this->_em->createQueryBuilder()
			->select('t', 'ta', 'tt', 's', 'l', 'b', 'ol', 'tl', 'r')
			->from($this->getEntityName(), 't')
			->leftJoin('t.textAuthors', 'ta')
			->leftJoin('t.textTranslators', 'tt')
			->leftJoin('t.series', 's')
			->leftJoin('t.labels', 'l')
			->leftJoin('t.books', 'b')
			->leftJoin('t.origLicense', 'ol')
			->leftJoin('t.transLicense', 'tl')
			->leftJoin('t.curRev', 'r')
			->where('t.id = ?1')->setParameter(1, $id)
			->getQuery()
			->useResultCache(true, static::DEFAULT_CACHE_LIFETIME)
			->getSingleResult();
	}

	/**
	 * @param string $prefix
	 * @param int $page
	 * @param int $limit
	 * @param string $orderBy
	 * @return array
	 */
	public function getByPrefix($prefix, $page = 1, $limit = null, $orderBy = null) {
		$orderBy = $this->normalizeOrderBy($orderBy);
		try {
			$ids = $this->getIdsByPrefix($prefix, $page, $limit, $orderBy);
		} catch (NoResultException $e) {
			return [];
		}
		return $this->getByIds($ids, $orderBy);
	}

	/**
	 * @param string $prefix
	 * @param int $page
	 * @param int $limit
	 * @param string $orderBy
	 * @return array
	 * @throws NoResultException
	 */
	public function getIdsByPrefix($prefix, $page, $limit, $orderBy) {
		$where = $prefix ? "WHERE t.title LIKE '$prefix%'" : '';
		$dql = "SELECT t.id FROM {$this->getEntityName()} t $where ORDER BY t.$orderBy";
		$query = $this->setPagination($this->_em->createQuery($dql), $page, $limit);
		$query->useResultCache(true, static::DEFAULT_CACHE_LIFETIME);
		$ids = $query->getResult('id');
		if (empty($ids)) {
			throw new NoResultException;
		}
		return $ids;
	}

	/**
	 * @param string $prefix
	 * @return int
	 */
	public function countByPrefix($prefix) {
		$where = $prefix ? "WHERE t.title LIKE '$prefix%'" : '';
		$dql = sprintf('SELECT COUNT(t.id) FROM %s t %s', $this->getEntityName(), $where);
		$query = $this->_em->createQuery($dql);
		$query->useResultCache(true, static::DEFAULT_CACHE_LIFETIME);
		return $query->getSingleScalarResult();
	}

	/**
	 * @param string $type
	 * @param int $page
	 * @param int $limit
	 * @param string $orderBy
	 * @return array
	 */
	public function getByType($type, $page = 1, $limit = null, $orderBy = null) {
		$orderBy = $this->normalizeOrderBy($orderBy);
		try {
			$ids = $this->getIdsByType($type, $page, $limit, $orderBy);
		} catch (NoResultException $e) {
			return [];
		}
		return $this->getByIds($ids, $orderBy);
	}

	/**
	 * @param string $type
	 * @param int $page
	 * @param int $limit
	 * @param string $orderBy
	 * @return array
	 * @throws NoResultException
	 */
	protected function getIdsByType($type, $page, $limit, $orderBy) {
		$where = "WHERE t.type = '$type'";
		$dql = "SELECT t.id FROM {$this->getEntityName()} t $where ORDER BY t.$orderBy";
		$query = $this->setPagination($this->_em->createQuery($dql), $page, $limit);
		$query->useResultCache(true, static::DEFAULT_CACHE_LIFETIME);
		$ids = $query->getResult('id');
		if (empty($ids)) {
			throw new NoResultException;
		}
		return $ids;
	}

	/**
	 * @param string $type
	 * @return int
	 */
	public function countByType($type) {
		$where = "WHERE t.type = '$type'";
		$dql = sprintf('SELECT COUNT(t.id) FROM %s t %s', $this->getEntityName(), $where);
		$query = $this->_em->createQuery($dql);
		$query->useResultCache(true, static::DEFAULT_CACHE_LIFETIME);
		return $query->getSingleScalarResult();
	}

	/**
	 * @param array $labels
	 * @param int $page
	 * @param int $limit
	 * @param string $orderBy
	 * @return array
	 */
	public function getByLabel($labels, $page = 1, $limit = null, $orderBy = null) {
		$orderBy = $this->normalizeOrderBy($orderBy);
		try {
			$ids = $this->getIdsByLabel($labels, $page, $limit, $orderBy);
		} catch (NoResultException $e) {
			return [];
		}
		return $this->getByIds($ids, $orderBy);
	}

	/**
	 * @param array $labels
	 * @param int $page
	 * @param int $limit
	 * @param string $orderBy
	 * @return array
	 * @throws NoResultException
	 */
	protected function getIdsByLabel($labels, $page, $limit, $orderBy) {
		$dql = sprintf("SELECT DISTINCT t.id FROM %s t JOIN t.labels l WHERE l.id IN (%s) ORDER BY t.$orderBy", $this->getEntityName(), implode(',', $labels));
		$query = $this->setPagination($this->_em->createQuery($dql), $page, $limit);
		$query->useResultCache(true, static::DEFAULT_CACHE_LIFETIME);
		$ids = $query->getResult('id');
		if (empty($ids)) {
			throw new NoResultException;
		}
		return $ids;
	}

	/**
	 * RAW_SQL
	 * @param array $labelIds
	 * @return int
	 */
	public function countByLabel($labelIds) {
		$labelIdsSearch = implode(',', $labelIds);
		return $this->fetchFromCache('TextCountByLabel_'.$labelIdsSearch, function() use ($labelIdsSearch) {
			return $this->_em->getConnection()->fetchColumn(sprintf('SELECT COUNT(DISTINCT tl.text_id) FROM text_label tl WHERE tl.label_id IN (%s)', $labelIdsSearch), [], 0);
		});
	}

	/**
	 * @param Person $author
	 * @param bool $groupBySeries
	 * @return array
	 */
	public function findByAuthor($author, $groupBySeries = true) {
		$texts = $this->getQueryBuilder()
			->leftJoin('e.textAuthors', 'ta')
			->where('ta.person = ?1')->setParameter(1, $author->getId())
			->orderBy('s.name, e.sernr, e.type, e.title')
			->getQuery()
			->useResultCache(true, static::DEFAULT_CACHE_LIFETIME)
			->getResult();
		if ($groupBySeries) {
			$texts = $this->groupTexts($texts);
		}
		return $texts;
	}

	/**
	 * @param Person $translator
	 * @return array
	 */
	public function findByTranslator($translator) {
		$texts = $this->getQueryBuilder()
			->leftJoin('e.textTranslators', 'tt')
			->where('tt.person = ?1')->setParameter(1, $translator->getId())
			->orderBy('e.type, e.title')
			->getQuery()
			->useResultCache(true, static::DEFAULT_CACHE_LIFETIME)
			->getResult();
		$texts = $this->groupTexts($texts, false);
		return $texts;
	}

	/**
	 * @param array $ids
	 * @param string $orderBy
	 * @return Text[]
	 */
	public function getByIds($ids, $orderBy = null) {
		$texts = $this->getQueryBuilder($orderBy)
			->where(sprintf('e.id IN (%s)', implode(',', $ids)))
			->getQuery()
			->useResultCache(true, static::DEFAULT_CACHE_LIFETIME)
			->getResult();
		return $texts;
	}

	/**
	 * @param string $title
	 * @param int $limit
	 * @return Text[]
	 */
	public function getByTitles($title, $limit = null) {
		$q = $this->getQueryBuilder()
			->where('e.title LIKE ?1 OR e.subtitle LIKE ?1 OR e.origTitle LIKE ?1')
			->setParameter(1, $this->stringForLikeClause($title))
			->getQuery();
		$q->useResultCache(true, static::DEFAULT_CACHE_LIFETIME);
		$this->addLimitingToQuery($q, $limit);
		return $q->getResult();
	}

	/**
	 * @param string $orderBys
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getQueryBuilder($orderBys = null) {
		return parent::getQueryBuilder($orderBys)
			->select('e', 'a', 'ap', 't', 'tp', 's', 'lang', 'olang')
			->leftJoin('e.series', 's')
			->leftJoin('e.textAuthors', 'a')
			->leftJoin('a.person', 'ap')
			->leftJoin('e.textTranslators', 't')
			->leftJoin('t.person', 'tp')
			->leftJoin('e.lang', 'lang')
			->leftJoin('e.origLang', 'olang');
	}

	/**
	 * @param Series $series
	 * @return Text[]
	 */
	public function getBySeries($series) {
		$texts = $this->_em->createQueryBuilder()
			->select('e', 'a', 'ap', 'ol', 'tl', 'lang', 'olang')
			->from($this->getEntityName(), 'e')
			->leftJoin('e.textAuthors', 'a')
			->leftJoin('a.person', 'ap')
			->leftJoin('e.origLicense', 'ol')
			->leftJoin('e.transLicense', 'tl')
			->leftJoin('e.lang', 'lang')
			->leftJoin('e.origLang', 'olang')
			->where('e.series = ?1')->setParameter(1, $series->getId())
			->addOrderBy('e.sernr, e.title')
			->getQuery()
			->useResultCache(true, static::DEFAULT_CACHE_LIFETIME)
			->getResult();
		return $texts;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function getByQuery($params) {
		return WorkSteward::joinPersonKeysForTexts(parent::getByQuery($params));
	}

	/**
	 * @return array
	 */
	public function getCountsByType() {
		$counts = $this->_em->createQueryBuilder()
			->select('e.type', 'COUNT(e.id)')
			->from($this->getEntityName(), 'e')
			->groupBy('e.type')
			->getQuery()
			->useResultCache(true, static::DEFAULT_CACHE_LIFETIME)
			->getResult('key_value');
		arsort($counts);
		return $counts;
	}

	/**
	 * @param string $where
	 * @return int
	 */
	public function getRandomId($where = '') {
		return parent::getRandomId("e.removedNotice IS NULL");
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function isValidType($type) {
		return in_array($type, self::$types);
	}

	public function getTypes() {
		return self::$types;
	}

	/**
	 * @param Text[] $texts
	 * @param bool $groupBySeries
	 * @return array
	 */
	protected function groupTexts($texts, $groupBySeries = true) {
		$bySeries = $byType = [];

		foreach ($texts as $text) {
			if ($groupBySeries && $text->getSeries()) {
				$bySeries[ $text->getSeries()->getId() ]['data'] = $text->getSeries();
				$bySeries[ $text->getSeries()->getId() ]['texts'][$text->getId()] = $text;
			} else {
				$byType[ $text->getType() ]['data']['name'] = $text->getType();
				$byType[ $text->getType() ]['texts'][$text->getId()] = $text;
			}
		}
		return $bySeries + $byType;
	}

	protected function normalizeOrderBy($orderBy) {
		if ($orderBy === null) {
			return $this->defaultSortingField;
		}
		$orderByParts = explode(' ', str_replace('-', ' ', $orderBy));
		if (count($orderByParts) == 1) {
			$orderByParts[] = 'asc';
		}
		list($field, $order) = $orderByParts;
		if (!in_array($field, $this->sortableFields) || !in_array(strtolower($order), ['asc', 'desc'])) {
			return $this->defaultSortingField;
		}
		return "$field $order";
	}
}
