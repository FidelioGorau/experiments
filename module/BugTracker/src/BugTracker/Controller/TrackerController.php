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
        $auth = $this->getServiceLocator()->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            echo $user_edit = $auth->getIdentity()->getId();
        }

        $posts = $objectManager
            ->getRepository('\BugTracker\Entity\BugList')
            ->findBy(array('state' => 1), array('created' => 'DESC'));
        $view = new ViewModel(array(
            'posts' => $posts,
        ));

        return $view;
    }

    public function activeAction()
    {
        $auth = $this->getServiceLocator()->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            return $this->getBugsLists(1);
        }
    }

    public function resolvedAction()
    {
        $auth = $this->getServiceLocator()->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            return $this->getBugsLists(2);
        }
    }

    public function closedAction()
    {
        return $this->getBugsLists(3);
    }


    public function addAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new \BugTracker\Form\BugForm($dbAdapter);
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $bug = new \BugTracker\Entity\BugList();
                $bug->exchangeArray($form->getData());
                $bug->setCreated(time());

                $objectManager->persist($bug);
                $objectManager->flush();

                // Redirect to list of blogposts
                return $this->redirect()->toRoute('bugtracker');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {
        // Create form.
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new \BugTracker\Form\BugForm($dbAdapter);
        $form->get('submit')->setValue('Save');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromRoute('id', 0);

            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $post = $objectManager
                ->getRepository('\BugTracker\Entity\BugList')
                ->findOneBy(array('id' => $id));
            if (!$post) {
                return $this->redirect()->toRoute('bugtracker');
            }
            // Fill form data.
            $form->bind($post);
            return array('form' => $form);
        }
        else {
            $form->setData($request->getPost());
            if($form->isValid()){
                $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $data = $form->getData();
                $id = $data['id'];
                try {
                    $bug = $objectManager->find('\BugTracker\Entity\BugList', $id);
                } catch (\Exception $ex) {
                    return $this->redirect()->toRoute('bugtracker', array(
                        'action' => 'index'
                    ));
                }
                $bug->exchangeArray($form->getData());
                $objectManager->persist($bug);
                $objectManager->flush();
                // Redirect to list of bugs
                return $this->redirect()->toRoute('bugtracker');
            }
        }
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $post = $objectManager
            ->getRepository('\BugTracker\Entity\BugList')
            ->findOneBy(array('id' => $id));

        if (!$post) {
            return $this->redirect()->toRoute('');
        }

        $view = new ViewModel(array(
            'post' => $post->getArrayCopy(),
        ));

        return $view;
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('bugtracker');
        }
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                try {
                    $bug = $objectManager->find('\BugTracker\Entity\BugList', $id);
                    $objectManager->remove($bug);
                    $objectManager->flush();
                }
                catch (\Exception $ex) {
                    return $this->redirect()->toRoute('bugtracker', array(
                        'action' => 'index'
                    ));
                }
            }
            return $this->redirect()->toRoute('bugtracker');
        }
        return array(
            'id' => $id,
            'post' => $objectManager->find('\BugTracker\Entity\BugList', $id)->getArrayCopy(),
        );
    }


    /**
     * @param $state
     * @return ViewModel
     */
    private function getBugsLists($state){
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $sm = $this->getServiceLocator();
        $auth = $sm->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            echo $user_edit = $auth->getIdentity()->getId();
        }
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $posts = $objectManager
            ->getRepository('\BugTracker\Entity\BugList')
            ->findBy(array('userId' => $auth->getIdentity()->getId(),'state' => $state), array('created' => 'DESC'));

        $view = new ViewModel(array(
            'posts' => $posts,
        ));

        return $view;
    }
}