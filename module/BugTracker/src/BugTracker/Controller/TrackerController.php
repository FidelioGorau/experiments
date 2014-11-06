<?php
namespace BugTracker\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use BugTracker\Entity;

/**
 * Class TrackerController
 * @package BugTracker\Controller
 */
class TrackerController extends AbstractActionController
{

    /**
     * indexAction
     * Function return list of active bugs.
     * @return ViewModel
     */
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

    /**
     * activeAction
     * Function returns a list of active bugs assigned to the current user.
     * @return ViewModel
     */
    public function activeAction()
    {
        return $this->getBugsLists(1);
    }

    /**
     * resolvedAction
     * Function returns a list of resolved bugs assigned to the current user.
     * @return ViewModel
     */
    public function resolvedAction()
    {
        return $this->getBugsLists(2);
    }

    /**
     * closedAction
     * Function returns a list of closed bugs assigned to the current user.
     * @return ViewModel
     */
    public function closedAction()
    {
        return $this->getBugsLists(3);
    }


    /**
     * addAction
     * Function to create a new bug.
     * @return array|\Zend\Http\Response
     * @throws \Zend\Mvc\Exception\DomainException
     */
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

    /**
     * editAction
     * Function for changing bug
     * @throws \Zend\Mvc\Exception\DomainException
     * @throws \Zend\Mvc\Exception\RuntimeException
     * @internal param int $id bug id
     * @return \Zend\Http\Response
     */
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
        } else {
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

    /**
     * viewAction
     * Function view current bug
     * @throws \Zend\Mvc\Exception\DomainException
     * @throws \Zend\Mvc\Exception\RuntimeException
     * @internal param int $id bug id
     * @return \Zend\Http\Response|ViewModel
     */
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

    /**
     *  deleteAction
     * Function del. current bug
     * @internal param int $id bug id
     * @return array|\Zend\Http\Response
     */
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
                } catch (\Exception $ex) {
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
     * getBugsLists
     * Private function returns a list of bugs with selected status
     * @param $state
     * @return ViewModel
     */
    private function getBugsLists($state)
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $sm = $this->getServiceLocator();
        $auth = $sm->get('zfcuser_auth_service');
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
