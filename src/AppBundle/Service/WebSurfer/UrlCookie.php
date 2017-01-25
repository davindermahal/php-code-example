<?php
/**
 * UrlCookie is used as part of the WebSurfer Service to store the URL a user has submitted and wishes to view
 * in the iFrame.  This class is separated from the WebSurferService itself for the Single Responsibility Principle.
 * This class manages the Cookie used to store this information.  It sets the cookie and also reads it back, validating
 * it to make sure no one has tampered with the cookie value and it is a valid URL still, before returning it back
 * to the WebSurferService to use.
 */
namespace AppBundle\Service\WebSurfer;

use DateTime;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Url as UrlConstraint;

class UrlCookie
{
    const URL_COOKIE_NAME = 'viewUrl';

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return string
     */
    public function getValidUrl(Request $request, ValidatorInterface $validator)
    {
        $cookieValue = $request->cookies->get(self::URL_COOKIE_NAME, '');
        if ($this->isValid($cookieValue, $validator)) {
            return $cookieValue;
        }

        return '';
    }

    /**
     * setCookie creates a new Cookie object and set it on the Response Object
     *
     * @param Response $response
     * @param $url
     */
    public function setCookie(Response $response, $url)
    {
        $response->headers->setCookie(new Cookie(self::URL_COOKIE_NAME, $url, new DateTime("+30 days"), '/'));
        $response->send();
    }

    /**
     * Symfony has it's own validation system with a validator that checks if a string is a valid URL.
     * isValid() uses the URL constraint to validate whether the string is indeed a URL or not.
     *
     * @param $url
     * @param ValidatorInterface $validator
     * @return bool
     */
    private function isValid($url, ValidatorInterface $validator)
    {
        $errors = $validator->validate($url, new UrlConstraint());
        return ($errors->count() == 0 ? true : false);
    }
}
