<?php
// src/AppBundle/Controller/PageController.php

namespace AppBundle\Controller;

use AppBundle\Entity\Enquiry;
use AppBundle\Form\EnquiryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/")
 */
class PageController extends Controller
{
    /**
     * @Route("/", name="app_page_index")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()
            ->getEntityManager();

        $blogs = $em->getRepository('AppBundle:Blog')
            ->getLatestBlogs();

        return $this->render('AppBundle:Page:index.html.twig', array(
            'blogs' => $blogs
        ));
    }

    /**
     * @Route("/about", name="app_page_about")
     * @Method({"GET", "POST"})
     */
    public function aboutAction()
    {
        return $this->render('AppBundle:Page:about.html.twig');
    }

    /**
 * @Route("/contact", name="app_page_contact")
 * @Method({"GET", "POST"})
 */
    public function contactAction()
    {
        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $message = \Swift_Message::newInstance()
                    ->setSubject('Contact enquiry from symblog')
                    ->setFrom('enquiries@symblog.co.uk')
                    ->setTo($this->container->getParameter('AppBundle.emails.contact_email'))
                    ->setBody($this->renderView('AppBundle:Page:contactEmail.txt.twig', array('enquiry' => $enquiry)));
                $this->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('success', 'Votre demande de contact a bien été envoyée. Merci!');

                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('AppBundle_contact'));
            }
        }

        return $this->render('AppBundle:Page:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/")
     */
    public function sidebarAction()
    {
        $em = $this->getDoctrine()
            ->getEntityManager();

        $tags = $em->getRepository('AppBundle:Blog')
            ->getTags();

        $tagWeights = $em->getRepository('AppBundle:Blog')
            ->getTagWeights($tags);

        $commentLimit   = $this->container
            ->getParameter('AppBundle.comments.latest_comment_limit');
        $latestComments = $em->getRepository('AppBundle:Comment')
            ->getLatestComments($commentLimit);

        return $this->render('AppBundle:Page:sidebar.html.twig', array(
            'latestComments'    => $latestComments,
            'tags'              => $tagWeights
        ));
    }

}