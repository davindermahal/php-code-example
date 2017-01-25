<?php
/**
 * WebSurferService is the service that handles the "Surf the web" portion of the page.
 * This class handles the form submission of a URL a user would like to visit and then checks that url against
 * the list of the urls stored in the system to see if it is meant to be blocked.   Once a URL is allowed, a cookie
 * must store this URL so if the user comes back to the page it remembers the url and again displays the iframe.
 *
 */
namespace AppBundle\Service\WebSurfer;

use AppBundle\Entity\Url;
use AppBundle\Form\UrlFormType;
use AppBundle\Lib\DAL\UrlDalInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WebSurferService
{
    private $urlDataAccess;
    private $isBlocked;
    private $form;
    /**
     * @var string urlToView is the url string used in the iFrame
     */
    private $urlToView;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var UrlCookie
     */
    private $cookie;

    /**
     * WebSurfer constructor.
     * @param UrlDalInterface $urlDataAccess
     * @param FormFactoryInterface $formFactory
     * @param ValidatorInterface $validator
     * @param UrlCookie $cookie
     */
    public function __construct(UrlDalInterface $urlDataAccess, FormFactoryInterface $formFactory,
                                ValidatorInterface $validator, UrlCookie $cookie)
    {
        $this->urlDataAccess = $urlDataAccess;
        $this->form = $formFactory->create(UrlFormType::class);
        $this->isBlocked = false;
        $this->validator = $validator;
        $this->cookie = $cookie;
    }

    public function getFormView()
    {
        return $this->form->createView();
    }

    public function getUrlToView()
    {
        return $this->urlToView;
    }

    /**
     * @return bool Is the UrlToView url string in the list of URL entities that are on the blocked list?
     */
    public function isBlocked()
    {
        return $this->isBlocked;
    }

    /**
     * Handle request is the primary business logic method called from the controller.
     * Bind the Request to the Url Form object, and then see if it's been submitted or not. Is it valid, and if so,
     * set a new cookie via the UrlCookie object.  If the form has not been submitted, then read the cookie and check
     * if it's valid before checking if the url, if any, is in the blocked list.
     *
     * @param Request $request
     * @param Response $response
     */
    public function handleRequest(Request $request, Response $response)
    {
        $this->form->handleRequest($request);

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            // New URL to view
            $data = $this->form->getData();
            $this->urlToView = $data->getUrl();
            $this->cookie->setCookie($response, $this->urlToView);
        } else {
            // Read Cookie to get any saved URL
            $this->urlToView = $this->cookie->getValidUrl($request, $this->validator);
        }

        $this->checkUrlIsBlocked($this->urlToView, $this->urlDataAccess->findByUrl($this->urlToView));
    }

    /**
     * The iFrame needs to be updated if the URL that's been removed was the URL that had been
     * attempted to be viewed. If this is the case, we need to update the iFrame.
     *
     * @param Request $request
     * @param $urlEntities
     * @return bool
     */
    public function shouldRefreshIframe(Request $request, $urlEntities)
    {
        $this->urlToView = $this->cookie->getValidUrl($request, $this->validator);
        return $this->checkUrlIsBlocked($this->urlToView, $urlEntities);
    }

    /**
     * Given a url string, check if it is in the array of Url Entities.
     *
     * @param string $url
     * @param Url[] $urlsEntities
     * @return bool
     */
    private function checkUrlIsBlocked($url, $urlsEntities)
    {
        if ('' === $url) {
            return false;
        }

        foreach ($urlsEntities as $entity) {
            if ($entity->getUrl() === $url) {
                $this->isBlocked = true;
                return true;
            }
        }

        return false;
    }
}
