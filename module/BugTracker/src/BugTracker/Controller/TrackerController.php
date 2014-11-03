<?php
namespace BugTracker\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use BugTracker\Entity;

class TrackerController extends AbstractActionController
{

    public function indexAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $posts = $objectManager
            ->getRepository('\BugTracker\Entity\BugList')
            ->findBy(array('state' => 1), array('created' => 'DESC'));

        $view = new ViewModel(array(
            'posts' => $posts,
        ));

        return $view;
    }

    public function resolvedAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $posts = $objectManager
            ->getRepository('\BugTracker\Entity\BugList')
            ->findBy(array('state' => 2), array('created' => 'DESC'));

        $view = new ViewModel(array(
            'posts' => $posts,
        ));

        return $view;
    }

    public function closedAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $posts = $objectManager
            ->getRepository('\BugTracker\Entity\BugList')
            ->findBy(array('state' => 3), array('created' => 'DESC'));

        $view = new ViewModel(array(
            'posts' => $posts,
        ));

        return $view;
    }

    public function addAction()
    {
        $form = new \BugTracker\Form\BugForm();
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $bug = new \BugTracker\Entity\BugList();
                $bug->exchangeArray($form->getData());
                $bug->setCreated(time());
                $bug->setUserId(1); //!!!DANGER!!!! Hardcode. REWRITE!!!!!

                $objectManager->persist($bug);
                $objectManager->flush();

                // Redirect to list of blogposts
                return $this->redirect()->toRoute('bugtracker');
            }
        }
        return array('form' => $form);
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $post = $objectManager
            ->getRepository('\BugTracker\Entity\BugList')
            ->findOneBy(array('id' => $id));

        if (!$post) {
            return $this->redirect()->toRoute('blog');
        }

        $view = new ViewModel(array(
            'post' => $post->getArrayCopy(),
        ));

        return $view;
    }

}