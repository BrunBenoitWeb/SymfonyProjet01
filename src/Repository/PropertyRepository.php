<?php

namespace App\Repository;

use App\Entity\Picture;
use App\Entity\Property;
use App\Entity\PropertySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Property|null find($id, $lockMode = null, $lockVersion = null)
 * @method Property|null findOneBy(array $criteria, array $orderBy = null)
 * @method Property[]    findAll()
 * @method Property[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class PropertyRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Property::class);

    }

    /**
     * @param PropertySearch $search
     * @param int $page
     * @return Query
     */

    public function FindAllVisible(PropertySearch $search): Query
    {
        $query = $this->findVisibleQuery();

        if ($search->getMaxPrice()){
            $query = $query
                ->andWhere('p.price <= :maxprice')
                ->setParameter('maxprice', $search->getMaxPrice());
        }
        if ($search->getMinSurface()){
            $query = $query
                ->andWhere('p.surface >= :minsurface')
                ->setParameter('minsurface', $search->getMinSurface());
        }
        if ($search->getLat() && $search->getLng() && $search->getDistance()){
            $query = $query
                ->select('p')
                ->andWhere('(6353 * acos( cos( radians(p.lat) ) * cos( radians(:lat) ) * cos( radians(:lng) - radians(p.lng)) + sin(radians(p.lat)) * sin( radians(:lat)))) <= :distance')
                ->setParameter('lng', $search->getLng())
                ->setParameter('lat', $search->getLat())
                ->setParameter('distance', $search->getDistance());
        }

        if ($search->getOptions()->count() > 0 ){
            $k =0;
            foreach ($search->getOptions() as $option){
                $k++;
                $query = $query
                    ->andWhere(":option$k MEMBER OF p.options")
                    ->setParameter("option$k", $option);
            }
        }
        return $query->getQuery();
    }

    /**
     * @return Property[]
     */

    public function findLatest(): array
    {
        $properties= $this->findVisibleQuery()
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
        $this->hydratePicture($properties);
        return $properties;
    }



    private function findVisibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where('p.sold = false');
    }


    private function hydratePicture($properties)
    {
        if (method_exists($properties, 'getItems')) {
            $properties = $properties->getItems();
        }
        $pictures = $this->getEntityManager()->getRepository(Picture::class)->findForProperties($properties);
        foreach ($properties as $property) {
            /** @var $property Property */
            if ($pictures->containsKey($property->getId())) {
                $property->setPicture($pictures->get($property->getId()));
            }
        }
    }
}
