<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * As this app wants you to login to use on the filter page, we send the user to the filter_index route
     * immediately, thus relying on Symfony's security mechanism to either allow them in or to rediect them
     * to the login page.
     *
     * @Route("/", name="homepage")
     */

    public function indexAction()
    {
        return $this->redirectToRoute('filter_index');
    }
}
