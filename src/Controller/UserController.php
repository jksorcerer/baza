<?php
/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Service\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController.
 */
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * User service.
     *
     * @var UserServiceInterface UserServiceInterface
     */
    private UserServiceInterface $userService;

    /**
     * Translator Interface.
     *
     * @var TranslatorInterface TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Password hasher.
     *
     * @var UserPasswordHasherInterface UserPasswordHasherInterface
     */
    protected UserPasswordHasherInterface $passwordHasher;

    /**
     * Constructor.
     *
     * @param UserServiceInterface        $userService    User service
     * @param TranslatorInterface         $translation    Translation interaface
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(UserServiceInterface $userService, TranslatorInterface $translation, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userService = $userService;
        $this->translator = $translation;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'user_index',
        methods: 'GET'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $pagination = $this->userService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'user_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]

    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }


//    public function show(int $id): Response
//    {
//
//        // Retrieve the User entity using the $id from the database
//        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
//
//        if (!$user) {
//            throw $this->createNotFoundException('User not found');
//        }
//
//        return $this->render('user/show.html.twig', ['user' => $user]);
////        return $this->render('user/show.html.twig', ['user' => $user]);
//    }

    /**
     * Create action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'user_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $user = new User();
        $form = $this->CreateForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.user_success')
            );
            $this->redirectToRoute('app_login');
        }

        return $this->render(
            'user/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'user_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(
            UserType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render(
            'user/edit.html.twig',
            ['form' => $form->createView(), 'user' => $user]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User Entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'user_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    public function delete(Request $request, User $user): Response
    {
        $form = $this->createForm(
            FormType::class,
            $user,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('user_delete', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            if ($user === $this->getUser()) {
                $this->userService->delete($user);
                $request->getSession()->invalidate();
                $this->container->get('security.token_storage')->setToken(null);

                return $this->redirectToRoute('app_login');
            }

            if ($this->isGranted('ROLE_ADMIN')) {
                $this->userService->delete($user);

                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render(
            'user/delete.html.twig',
            ['form' => $form->createView(), 'user' => $user]
        );
    }
}
