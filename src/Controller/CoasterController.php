<?php

namespace App\Controller;

use App\Entity\Coaster;
use App\Form\CoasterType;
use App\Repository\CategoryRepository;
use App\Repository\CoasterRepository;
use App\Repository\ParkRepository;
use App\Security\Voter\CoasterVoter;
use App\Service\FileUploaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CoasterController extends AbstractController
{
    #[Route('/coaster/')]
    public function index(
        CoasterRepository $coasterRepository,
        ParkRepository $parkRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response
    {
        $parkId = (int) $request->get('park', '');
        $categoryId = (int) $request->get('category', '');
        $search = $request->get('search', '');

        $itemCount = 10;
        $page = max($request->get('p', 1), 1);
        $begin = ($page - 1) * $itemCount;

        // $coasters = $coasterRepository->findAll();
        $coasters = $coasterRepository->findFiltered($parkId, $categoryId, $search, $itemCount, $begin);

        // dump($coasters);

        $pageCount = max(ceil($coasters->count() / $itemCount), 1);

        return $this->render('coaster/index.html.twig', [
            'coasters' => $coasters,
            'parks' => $parkRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
            'pageCount' => $pageCount,
        ]);
    }

    #[Route(path: '/coaster/add')]
    #[IsGranted('ROLE_USER')]
    public function add(EntityManagerInterface $em, Request $request, FileUploaderInterface $fileUploader): Response
    {
        $user = $this->getUser();

        $coaster = new Coaster();
        $coaster->setAuthor($user);
        /*$coaster->setName('Blue Fire')
            ->setMaxHeight(38)
            ->setMaxSpeed(100)
            ->setLength(1056)
            ->setOperating(true)
        ;*/

        $form = $this->createForm(CoasterType::class, $coaster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Donnée du champ "image"
            $image = $form->get('image')->getData();
            if ($image !== null) {
                $path = $fileUploader->upload($image);
                $coaster->setImageFileName($path);
            }
            // ajoute la nouvelle entité dans le manager Doctrine
            $em->persist($coaster);

            // Met à jour la DB
            $em->flush();

            return $this->redirectToRoute('app_coaster_index');
        }

        //dump($coaster);

        return $this->render('coaster/add.html.twig', [
            'coasterForm' => $form,
        ]);
    }

    #[Route('/coaster/{id}/edit')]
    public function edit(Coaster $coaster, Request $request, EntityManagerInterface $em, FileUploaderInterface $fileUploader): Response
    {
        $this->denyAccessUnlessGranted(CoasterVoter::EDIT, $coaster);
        
        $form = $this->createForm(CoasterType::class, $coaster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Donnée du champ "image"
            $image = $form->get('image')->getData();
            if ($image !== null) {
                if ($coaster->getImageFileName()) {
                    $fileUploader->remove($coaster->getImageFileName());
                }

                $path = $fileUploader->upload($image);
                $coaster->setImageFileName($path);
            }
            // Met à jour la DB
            $em->flush();

            return $this->redirectToRoute('app_coaster_index');
        }

        dump($coaster);

        return $this->render('coaster/edit.html.twig', [
            'coasterForm' => $form,
        ]);
    }

    #[Route('/coaster/{id}/delete')]
    public function delete(Coaster $coaster, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(CoasterVoter::EDIT, $coaster);
        
        if ($this->isCsrfTokenValid(
            'delete'.$coaster->getId(),
            $request->request->get('_token')
        )) {
            $em->remove($coaster);
            $em->flush();
        
            return $this->redirectToRoute('app_coaster_index');
        }
        
        return $this->render('coaster/delete.html.twig', [
            'coaster' => $coaster,
        ]);
    }
}