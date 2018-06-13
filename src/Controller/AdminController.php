<?php

namespace App\Controller;

use App\Entity\User;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface; // auto-wiring

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin_dashboard", methods="GET")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * [ ] show all users except for himself
     * 
     * @Route("/admin/users", name="users_list", methods="GET")
     */
    public function manageUsers(EntityManagerInterface $em)
    {
        $repository = $em->getRepository(User::class);
        
        $users = $repository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/user/create", name="create_user", methods="POST")
     */
    public function createUser(Request $request, EntityManagerInterface $em)
    {
        $user = new User();

        $user->setUsername($request->request->get('username'));
        $user->setEmail($request->request->get('email'));
        $user->setPassword($request->request->get('password'));

        $em->persist($user);
        $em->flush();

        // flash msg if registered correctly

        return $this->redirectToRoute('users_list');
    }

    /**
     * [ ] change methods to PUT
     * 
     * @Route("/admin/user/{id}/update", name="update_user",
     * requirements={"id" = "\d+"}, methods="GET")
     */
    public function updateUser(EntityManagerInterface $em, $id = 1)
    {
        $repository = $em->getRepository(User::class);
        $user =  $repository->find($id);

        dump($user);die;
        
        if (!$user) {
            throw $this->createNotFoundException('No user found for id '. $id);
        }

        // update field if needed
        $user->setName('New product name!');
        $entityManager->flush();
    }

    /**
     * @Route("/admin/categories", name="categories_list", methods="GET")
     */
    public function manageCategories(EntityManagerInterface $em)
    {
        dump(app.user.username);die;
    }

    /**
     * [ ] change methods to PUT
     * 
     * @Route("/admin/category/{id}/update", name="update_category",
     * requirements={"id"="\d+"}, methods="GET")
     */
    public function updateCategory(EntityManagerInterface $em, $id)
    {
        
    }
}

/**
 * [ ] check if user is admin
 */