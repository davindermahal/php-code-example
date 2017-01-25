<?php

namespace AppBundle\Lib\DAL;

use AppBundle\Entity\Url;

interface UrlDalInterface
{
    /**
     * @param string $url
     * @return Url[]
     */
    public function findByUrl($url);

    /**
     * @return Url[]
     */
    public function findAll();

    /**
     * @param array $ids
     * @return Url[]
     */
    public function findByIds($ids);

    /**
     * @param $urlEntity
     */
    public function save($urlEntity);

    /**
     * @param $urlEntity
     */
    public function remove($urlEntity);

    /**
     * @param array $urlEntities
     * @return
     */
    public function removeUrls($urlEntities);
}
