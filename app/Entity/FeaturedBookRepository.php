<?php namespace App\Entity;

/**
 *
 */
class FeaturedBookRepository extends EntityRepository {
	/**
	 * @param int $limit
	 * @return FeaturedBook[]
	 */
	public function getLatest($limit = null) {
		return $this->_em->createQueryBuilder()
			->from($this->getEntityName(), 'b')
			->select('b')
			->orderBy('b.id', 'desc')
			->getQuery()->setMaxResults($limit)
			->useResultCache(true, static::DEFAULT_CACHE_LIFETIME)
			->getArrayResult();
	}
}
