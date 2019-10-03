<?php

namespace JsonApi\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use JsonApi\Context\ContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package JsonApi\Controller
 */
abstract class Controller
{
    use ControllerTrait;

    const MIME_TYPE = 'application/vnd.api+json';

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @param ContextInterface $context
     * @throws QueryException
     */
    public function list(Request $request, Criteria $criteria, ContextInterface $context)
    {
        /** @var EntityManagerInterface $em */
        [$metadata, $associations, $selected, $alias] = $context->getInclude();
        $em = $this->getDoctrine()->getManagerForClass($metadata->rootEntityName);
        $qb = $em->createQueryBuilder()
            ->from($metadata->rootEntityName, $alias)
            ->select($alias);
        $this->addRelation($qb, $alias, $associations);
        $qb->addCriteria($criteria);
        $list = $qb->getQuery()->getResult();
        dump($list);
    }

    private function addRelation(QueryBuilder $qb, string $alias, array $associations)
    {
        foreach ($associations as $association => [$metadata, $childAssociations, $selected, $childAlias]) {
            $qb->leftJoin($alias.'.'.$association, $childAlias);
            if ($selected) {
                $qb->addSelect($childAlias);
            }
            $this->addRelation($qb, $childAlias, $childAssociations);
        }
    }
}
