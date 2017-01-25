<?php
/**
 * The FilterController is the primary controller for the application.
 * My goal was to create 'thin' controllers and to allow the core business logic to be in the Service classes.
 * These controllers use the appropriate Service, and then gather the data needed to display the templates.
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterController extends Controller
{
    /**
     * indexAction is the core page for the application. It displays, the list of URLs, a form to add URLs,
     * and the WebSurfer Form and iFrame.
     *
     * @Route("/filter", name="filter_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $webSurfer = $this->get('app.web_surfer_service');
        $webSurfer->handleRequest($request, new Response());

        $urlFilterService = $this->get('app.url_filter_service');

        return $this->render('filter/index.html.twig', [
            'urls_list'     => $urlFilterService->getListOfAllUrls(),
            'form'          => $webSurfer->getFormView(),
            'url_to_view'   => $webSurfer->getUrlToView(),
            'blocked'       => $webSurfer->isBlocked(),
        ]);
    }

    /**
     * addAction can display and handle the form submission for adding a URL.
     * I use a modal to display the addForm in the indexAction method, and then process the form submission in this
     * action.  If needed, this action can also display the add form without the modal at the route annotated below.
     *
     * @Route("/filter/add", name="filter_add")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $urlFilterService = $this->get('app.url_filter_service');

        if ($urlFilterService->save($request, $this->get('session'))) {
            return $this->redirectToRoute('filter_index');
        }

        $template = ($request->attributes->get('isModal', false) === true) ? 'add-modal.html.twig' : 'add.html.twig';

        return $this->render("filter/{$template}", array(
            'form' => $urlFilterService->getFormView(),
            'url' => $this->generateUrl('filter_add')
        ));
    }


    /**
     * removeAction method removes/deletes the URL from the database.
     * Here is it processed as an AJAX request, deletes the appropriate URL ids, and then returns data back as a
     * JSON request.  Not only are the URLs deleted, but there is a situation where one of the URLs being deleted
     * is the one trying to be viewed via the Web Surfer.  In that case, we must refresh the iFrame. As you can see,
     * the Service classes do the bulk of the work, and the controllers only create the views to be sent back to the
     * user.
     *
     * @Route("filter/remove", name="filter_remove")
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $urlFilterService = $this->get('app.url_filter_service');
            $data = $urlFilterService->remove($request);

            if ($request->request->get('isXhr', false)) {
                $jsonData = [
                    'isSuccess' => true,
                    'items' => $data['ids'],
                    'iframeView' => false,
                ];

                if (!empty($data['entities'])) {
                    // If the URL in the iFrame is one of the deleted URLs then we must refresh the iFrame
                    $webSurfer = $this->get('app.web_surfer_service');
                    if ($webSurfer->shouldRefreshIframe($request, $data['entities'])) {
                        $iframeView = $this->renderView(
                            'filter/iframe.html.twig',
                            ['url_to_view' => $webSurfer->getUrlToView()]
                        );
                        $jsonData['iframeView'] = $iframeView;
                    }
                }

                return new JsonResponse($jsonData);
            }
        }

        return $this->redirectToRoute('filter_index');
    }
}
