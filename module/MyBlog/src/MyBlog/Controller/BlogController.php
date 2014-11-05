<?php
namespace MyBlog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use MyBlog\Entity;

class BlogController extends AbstractActionController
{

    public function indexAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $posts = $objectManager
            ->getRepository('\MyBlog\Entity\BlogPost')
            ->findBy(array('state' => 1), array('created' => 'DESC'));

        $view = new ViewModel(array(
            'posts' => $posts,
        ));

        return $view;
    }
    public function addAction()
    {
        $form = new \MyBlog\Form\BlogPostForm();
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->isValid();
           // if ($form->isValid()) {
                $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $blogpost = new \MyBlog\Entity\BlogPost();
                $blogpost->exchangeArray($form->getData());
                $blogpost->setCreated(time());
                $blogpost->setUserId(0);

                $objectManager->persist($blogpost);
                $objectManager->flush();

                // Redirect to list of blogposts
                return $this->redirect()->toRoute('blog');
          /*  }
            else {
                $message = 'Error while saving blogpost';
                $this->flashMessenger()->addErrorMessage($message);
            }*/
        }
        return array('form' => $form);
    }
    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $post = $objectManager
            ->getRepository('\MyBlog\Entity\BlogPost')
            ->findOneBy(array('id' => $id));

        if (!$post) {
            return $this->redirect()->toRoute('blog');
        }

        $view = new ViewModel(array(
            'post' => $post->getArrayCopy(),
        ));

        return $view;
    }

    public function editAction()
    {
// Create form.
        $form = new \MyBlog\Form\BlogPostForm();
        $form->get('submit')->setValue('Save');
        $request = $this->getRequest();
        if (!$request->isPost()) {
// Check if id and blogpost exists.
            $id = (int) $this->params()->fromRoute('id', 0);
            if (!$id) {
                return $this->redirect()->toRoute('blog');
            }
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $post = $objectManager
                ->getRepository('\MyBlog\Entity\BlogPost')
                ->findOneBy(array('id' => $id));
            if (!$post) {
                return $this->redirect()->toRoute('blog');
            }
// Fill form data.
            $form->bind($post);
            return array('form' => $form);
        }
        else {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $data = $form->getData();
                $id = $data['id'];
                try {
                    $blogpost = $objectManager->find('\MyBlog\Entity\BlogPost', $id);
                }
                catch (\Exception $ex) {
                    return $this->redirect()->toRoute('blog', array(
                        'action' => 'index'
                    ));
                }
                $blogpost->exchangeArray($form->getData());
                $objectManager->persist($blogpost);
                $objectManager->flush();
                $message = 'Blogpost succesfully saved!';
                $this->flashMessenger()->addMessage($message);
// Redirect to list of blogposts
                return $this->redirect()->toRoute('blog');
            }
            else {
                $message = 'Error while saving blogpost';
                $this->flashMessenger()->addErrorMessage($message);
            }
        }
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('blog');
        }
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                try {
                    $blogpost = $objectManager->find('MyBlog\Entity\BlogPost', $id);
                    $objectManager->remove($blogpost);
                    $objectManager->flush();
                }
                catch (\Exception $ex) {
                    return $this->redirect()->toRoute('blog', array(
                        'action' => 'index'
                    ));
                }
            }
            return $this->redirect()->toRoute('blog');
        }
        return array(
            'id' => $id,
            'post' => $objectManager->find('MyBlog\Entity\BlogPost', $id)->getArrayCopy(),
        );
    }
}