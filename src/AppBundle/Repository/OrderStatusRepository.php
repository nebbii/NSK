<?php

/*
 * Nexxus Stock Keeping (online voorraad beheer software)
 * Copyright (C) 2018 Copiatek Scan & Computer Solution BV
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see licenses.
 *
 * Copiatek – info@copiatek.nl – Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Repository;

use AppBundle\Entity\OrderStatus;

class OrderStatusRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param string $name
     * @param bool $isPurchase
     * @param bool $isSale
     * @return OrderStatus
     */
    public function findOrCreate($name, $isPurchase, $isSale)
    {
        $orderStatus = $this->findOneBy(array("name" => $name));

        if (!$orderStatus)
        {
            $orderStatus = new OrderStatus();
            $orderStatus->setName($name);
            $orderStatus->setIsSale($isSale);
            $orderStatus->setIsPurchase($isPurchase);
            $this->_em->persist($orderStatus);
        }

        return $orderStatus;
    }
}
