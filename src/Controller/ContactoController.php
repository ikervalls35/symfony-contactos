<?php
namespace App\Controller;

use App\Entity\Contacto;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{
    #[Route('/contacto/{codigo}', name: 'contacto', requirements: ['codigo' => '[0-9]+'])]

public function ficha(ManagerRegistry $doctrine, int $codigo = 1): Response
{
    $repositorio = $doctrine->getRepository(Contacto::class);
    $contacto = $repositorio->find($codigo);
    $html = "
    <h1>Detalle del contacto</h1>
    <p>Nombre: " . $contacto->getNombre() . "</p>
    <p>Teléfono: " . $contacto->getTelefono() . "</p>
    <p>Email: " . $contacto->getEmail() . "</p>
    ";
    return new Response($html);
}
}