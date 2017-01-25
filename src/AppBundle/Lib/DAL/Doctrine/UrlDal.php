<?php

/**
 * This application currently uses Doctrine as the Data Access Layer (DAL).  However, if I wanted to change the DAL
 * to another mechanism, like JSON or XML, I could do so by writing a concrete class that implements the URLDalInterface.
 * Once this new class has been created to interact with the desired data store, simply change the services.yml file to
 * include the new URL DAL instead of this Doctrine one.
 */

namespace AppBundle\Lib\DAL\Doctrine;

use AppBundle\Lib\DAL\UrlDalInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

class UrlDal implements UrlDalInterface
{
    /**
     * @var
     */
    private $repository;

    /**
     * @ORM\Column(type="string")
     */
    private $entityManager;

    /**
     * UrlDal constructor.
     * @param $repository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct($repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * Given a string URL, queries the database for the URL entity with this URL
     * @param string $url
     * @return UrlDal[]
     */
    public function findByUrl($url)
    {
        return $this->repository->findByUrl($url);
    }

    /**
     * Find all URLs from the database and populate them into the Url Entities.
     * @return UrlDal[]
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * Given an array of Ids, this method queries those URL entities
     * @param array $ids
     * @return UrlDal[]
     */
    public function findByIds($ids)
    {
        return $this->repository->findBy(['id' => $ids]);
    }

    /**
     * save(), given a URL entity, we save it to the database.
     * For simplicity sake, I override the exception with a nice message back to the user when they enter
     * a duplicate URL.
     * @param $urlEntity
     * @throws \Exception
     */
    public function save($urlEntity)
    {
        try {
            $this->entityManager->persist($urlEntity);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new \Exception("This is a duplicate URL");
        }
    }

    /**
     * Deletes a single Entity from the database
     * @param $urlEntity
     */
    public function remove($urlEntity)
    {
        $this->entityManager->remove($urlEntity);
        $this->entityManager->flush();
    }

    /**
     * Given an array of entities, I remove them from the database.
     * Calling flush() after, I tell the entity manager to remove them, instructs Doctrine to execute
     * this in one query.
     * @param array $urlEntities
     */
    public function removeUrls($urlEntities)
    {
        foreach ($urlEntities as $entity) {
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
    }
}
