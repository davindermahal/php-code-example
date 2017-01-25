<?php
/**
 * To keep thin controllers, UrlFilterService is called from the Filter controller and manages the Add/List/Remove
 * functionality of the URLs.
 */
namespace AppBundle\Service;

use AppBundle\Form\UrlFormType;
use AppBundle\Lib\DAL\UrlDalInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UrlFilterService
{
    /**
     * Stores the Data Access Layer for URL
     * @var UrlDalInterface
     */
    private $urlDal;

    /**
     * @var FormInterface
     */
    private $form;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * UrlFilterService constructor.
     * @param UrlDalInterface $urlDal
     * @param FormFactoryInterface $formFactory used to create the UrlFormType
     */
    public function __construct(UrlDalInterface $urlDal, FormFactoryInterface $formFactory)
    {
        $this->urlDal = $urlDal;
        $this->formFactory = $formFactory;
    }

    /**
     * Using the Data Access Layer, this calls the Repository to get all URL entities.
     *
     * @return \AppBundle\Entity\Url[]
     */
    public function getListOfAllUrls()
    {
        return $this->urlDal->findAll();
    }

    /**
     * save() handles the Request, to see if it's been submitted and if so, save the Url Entity.
     * Session Flashes are used to provide messaging to the user once the page reloads.
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return bool
     */
    public function save(Request $request, SessionInterface $session)
    {
        $this->createUrlForm();
        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $url = $this->form->getData();

                try {
                    $this->urlDal->save($url);
                    $session->getBag('flashes')->add('success', 'The URL has been added to the list.');
                } catch (\Exception $exception) {
                    $session->getBag('flashes')->add('error', $exception->getMessage());
                }
                return true;
            }
        }
    }

    /**
     * This method is called from the controller to get the "view" of the form object.
     * I have a method to call the createView because I want to keep the form property private so no one can
     * inadvertently manipulate it.
     *
     * @return mixed
     */
    public function getFormView()
    {
        return $this->form->createView();
    }

    /**
     * remove() Reads the Request and gets any items (the values of the checkboxes in the URL list) and extracts them.
     * I filter the array to validate the values provided are integers, before using the URL Data Access Layer to
     * retrieve the URL entities, and delete them.
     *
     * The returned data array contains the ids and entities that were deleted. This then provides the necessary
     * information to the controller so it can pass it over to the WebSurferService.
     *
     * An alternative method, to do this would be to listen to the postRemove event from Doctrine and check if the
     * current URL in the iframe was deleted.
     *
     * @param Request $request
     * @return array
     */
    public function remove(Request $request)
    {
        $items = explode(',', $request->request->get('items', []));

        $data['ids'] = array_filter($items, function ($item) {
            return is_int((int) $item);
        });

        if (!empty($data['ids'])) {
            $urlEntitiesRemoved = $this->urlDal->findByIds($data['ids']);
            $this->urlDal->removeUrls($urlEntitiesRemoved);
            $data['entities'] = $urlEntitiesRemoved;
        }

        return $data;
    }

    /**
     * Creates the URLFormType from the FormFactory.  This form object will be needed to handle the form processing.
     */
    private function createUrlForm()
    {
        $this->form = $this->formFactory->create(UrlFormType::class);
    }
}
