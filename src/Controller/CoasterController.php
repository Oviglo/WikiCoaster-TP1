<?php

namespace App\Controller;

use App\Entity\Coaster;
use App\Form\CoasterType;
use App\Repository\CoasterRepository;
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
    public function index(CoasterRepository $coasterRepository): Response
    {
        $coasters = $coasterRepository->findAll();

        return $this->render('coaster/index.html.twig', [
            'coasters' => $coasters,
        ]);
    }

    #[Route('coaster/{id}/edit')]
    public function edit(Coaster $coaster, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CoasterType::class, $coaster);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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