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
 * Copiatek � info@copiatek.nl � Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Filter;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class OnRequestListener
{
    protected $em;
    protected $tokenStorage;

    public function __construct($em, $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        if($this->tokenStorage->getToken())
        {
            $s = $this->tokenStorage;
            $t = $s->getToken();
            $user = $t->getUser();

            if(is_a($user, \AppBundle\Entity\User::class) && $user->getLocation()
               && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())
               && !in_array('ROLE_MANAGER', $user->getRoles()))
            {
                $filter = $this->em->getFilters()->enable('location');
                $filter->setParameter('locationId', $user->getLocation()->getId());
            }
        }
    }
}