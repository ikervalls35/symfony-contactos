<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Form\ContactoFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{
    // Portada de la web: PÚBLICA (no requiere estar logueado)
    #[Route('/index', name: 'inicio')]
    #[Route('/', name: 'home')]
    public function inicio(ManagerRegistry $doctrine): Response
    {
        $contactos = $doctrine->getRepository(Contacto::class)->findAll();

        return $this->render('index.html.twig', [
            'contactos' => $contactos,
        ]);
    }

    // Detalle del contacto: PÚBLICO para ver, PROTEGIDO si intenta borrar
    #[Route('/contacto/{codigo}', name: 'contacto', requirements: ['codigo' => '[0-9]+'])]
    public function ficha(ManagerRegistry $doctrine, Request $request, int $codigo = 1): Response
    {
        $contacto = $doctrine->getRepository(Contacto::class)->find($codigo);

        // Procesar borrado (solo si está logueado y envía acción 'borrar')
        if ($request->isMethod('POST') && $request->request->get('accion') === 'borrar') {
            if (!$this->getUser()) {
                return $this->redirectToRoute('app_login');
            }

            if ($contacto) {
                $entityManager = $doctrine->getManager();
                $entityManager->remove($contacto);
                $entityManager->flush();
            }

            return $this->redirectToRoute('inicio');
        }

        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto
        ]);
    }

    // Añadir nuevo contacto: PROTEGIDO (requiere estar logueado)
    #[Route('/contacto/nuevo', name: 'nuevo')]
    public function nuevo(ManagerRegistry $doctrine, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $contacto = new Contacto();
        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();

            return $this->redirectToRoute('contacto', ['codigo' => $contacto->getId()]);
        }

        return $this->render('nuevo.html.twig', [
            'formulario' => $formulario->createView()
        ]);
    }

    // Editar/Eliminar contacto: PROTEGIDO (requiere estar logueado)
    #[Route('/contacto/editar/{codigo}', name: 'editar', requirements: ['codigo' => '\d+'])]
    public function editar(ManagerRegistry $doctrine, Request $request, int $codigo): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $contacto = $doctrine->getRepository(Contacto::class)->find($codigo);

        if (!$contacto) {
            return $this->redirectToRoute('inicio');
        }

        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);

        // Control de los dos botones en la ventana de Editar
        if ($request->isMethod('POST')) {
            $accion = $request->request->get('accion');

            if ($accion === 'borrar') {
                $entityManager = $doctrine->getManager();
                $entityManager->remove($contacto);
                $entityManager->flush();

                return $this->redirectToRoute('inicio');
            }

            if ($accion === 'guardar' && $formulario->isSubmitted() && $formulario->isValid()) {
                $entityManager = $doctrine->getManager();
                $entityManager->flush();

                return $this->redirectToRoute('contacto', ['codigo' => $contacto->getId()]);
            }
        }

        return $this->render('editar.html.twig', [
            'formulario' => $formulario->createView(),
            'contacto' => $contacto,
        ]);
    }
}