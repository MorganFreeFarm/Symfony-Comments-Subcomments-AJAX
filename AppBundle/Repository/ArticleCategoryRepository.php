<?php
declare(strict_types = 1);
namespace AppBundle\Repository;

/**
 * ArticleCategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleCategoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function getCategoryIdByCode(string $code) : array
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query
            ->select('a.id')
            ->from('AppBundle:ArticleCategory', 'a')
            ->andWhere('a.code = :code')
            ->setParameter('code', $code)
            ->getQuery();

        return $query->getQuery()->getResult();
    }
}
