<?php

namespace App\Controller;

use App\Entity\Coaster;
use App\Form\CoasterType;
use App\Repository\CategoryRepository;
use App\Repository\CoasterRepository;
use App\Repository\ParkRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CoasterController extends AbstractController
{
    #[Route('coaster/add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $entity = new Coaster();
        $user = $this->getUser();

        $form = $this->createForm(CoasterType::class, $entity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('app_coaster_index');
        }

        return $this->render('coaster/add.html.twig', [
            'coasterForm' => $form,
        ]);
    }

    #[Route('coaster/')]
    public function index(Request $request, CoasterRepository $coasterRepository, ParkRepository $parkRepository, CategoryRepository $categoryRepository): Response
    {
        
        $parks = $parkRepository->findBy([], ['name' => 'ASC']);
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);
        $itemCount = 5;
        $page = $request->get('p', 1);

        $coasters = $coasterRepository->findFiltered(
            $request->query->get('park',''),
            $request->query->get('category',''),
            $page,
            $itemCount
        );
        
        $pageCount = max(ceil($coasters->count() / $itemCount), 1);

        return $this->render('coaster/index.html.twig', [
            'coasters' => $coasters,
            'parks' => $parks,
            'categories' => $categories,
            'pageCount' => $pageCount,
            'p' => $page,
        ]);
    }

    #[Route('coaster/{id}/edit')]
    public function edit(Coaster $coaster, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $coaster);
        
        $form = $this->createForm(CoasterType::class, $coaster);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $fileName = $fileUploader->upload($imageFile);
                $coaster->setImageFileName($fileName);
            }

            $em->flush();

            return $this->redirectToRoute('app_coaster_index');
        }

        return $this->render('coaster/edit.html.twig', [
            'coasterForm' => $form,
        ]);
    }

    #[Route('coaster/{id}/delete')]
    public function delete(Request $request, Coaster $coaster, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coaster->getId(), $request->request->get('_token'))) {
            $em->remove($coaster);
            $em->flush();

            return $this->redirectToRoute('app_coaster_index');
        }

        return $this->render('coaster/delete.html.twig', [
            'coaster' => $coaster,
        ]);
    }
}