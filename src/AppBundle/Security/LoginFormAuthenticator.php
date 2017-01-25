<?php
/**
 * LoginFormAuthenticator uses Symfony's Security bundle for authentication.  I have added some extra code based on
 * my preferences for security such as changing the error message to a generic one. At this time, I have not fully
 * implemented the password hashing mechanism, but hard coded a password (never to be done with a real production app!)
 */
namespace AppBundle\Security;

use AppBundle\Form\LoginForm;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $formFactory;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $AuthenticationErrorMessage = "Sorry, login failed.  Invalid username or password";

    public function __construct(FormFactoryInterface $formFactory, EntityManager $em, RouterInterface $router)
    {
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->router = $router;
    }

    public function getCredentials(Request $request)
    {
        $isLoginSubmit = $request->getPathInfo() == '/login' && $request->isMethod('POST');
        if (!$isLoginSubmit) {
            // skip auth
            return;
        }

        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);

        $data = $form->getData();

        // Save the username in the session so it can be displayed on the form
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['_username']
        );

        return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];
        $user = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

        /**
         * I am overriding the error message provided by Symfony Security component because it is best practice not to
         * provide any information other than the login did not work.  Providing an error such as bad password, or
         * username not found leaks information to potential hackers. Instead, simply provide a basic error message.
         */

        if (is_null($user)) {
            throw new CustomUserMessageAuthenticationException($this->AuthenticationErrorMessage);
        }

        return $user;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     *
     * Password is hard coded for this coding project
     * @todo use bcrypt to create a salted password.
     * For extra protection, add pepper (a code based hash) to the code so if there is a database leak,
     * the pepper would still be needed to break the password
     *
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['_password'];
        if ($password == 'rabbit') {
            return true;
        }

        // Security Best Practice
        // override the error message to prevent information leakage
        throw new CustomUserMessageAuthenticationException($this->AuthenticationErrorMessage);
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('filter_index');
    }
}
