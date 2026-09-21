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
    return $this->render('ficha_contacto.html.twig', [
        'contacto' => $contacto,
    ]);
}

#[Route('/contacto/nuevo/{nombre}/{telefono}/{email}', name: 'nuevo-con-datos')]
public function nuevoConDatos(ManagerRegistry $doctrine, string $nombre, string $telefono, string $email)
{
    $contacto = new Contacto();
    $contacto->setNombre($nombre);
    $contacto->setTelefono($telefono);
    $contacto->setEmail($email);
    $entityManager = $doctrine->getManager();
    $entityManager->persist($contacto);
    $entityManager->flush();
    return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
}
#[Route('/contacto/update/{codigo?1}', name: 'update')]
public function update(ManagerRegistry $doctrine, $codigo): Response
{
    $entityManager = $doctrine->getManager();
    $repositorio = $doctrine->getRepository(Contacto::class);
    $contacto = $repositorio->find($codigo);
    $contacto->setNombre("Nombre cambiado");
    $entityManager->persist($contacto);

    try{
        $entityManager->flush();
        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);

    }catch (\Exception $e){
        return new Response("Se ha producido un error: " . $e->getMessage());
    }
}
 #[Route('/contacto/borrar/{codigo}', name:'borrar')]
    public function borrar(ManagerRegistry $doctrine, int $codigo){
        $contacto = $doctrine->getRepository(Contacto::class)->find($codigo);
        
        if ($contacto){
            $entityManager = $doctrine->getManager();

            try{
                $entityManager->remove($contacto);
                $entityManager->flush();
                return $this->redirectToRoute('inicio');

            }catch(\Exception $e){
                error_log('Error al eliminar el contacto: ' . $e->getMessage());
                return new Response ("Error eliminando objeto".$e->getMessage());
            }
            
        } else{
            return new Response("No se ha encontrado el contacto");
        }
    }
}