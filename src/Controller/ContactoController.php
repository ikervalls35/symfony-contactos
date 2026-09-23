<?php
namespace App\Controller;

use App\Entity\Contacto;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactoFormType;
use Symfony\Component\HttpFoundation\Request;

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
    #[Route('/contacto/nuevo', name: 'nuevo')]
public function nuevo(ManagerRegistry $doctrine, Request $request)
{
    $contacto = new Contacto();
    $formulario = $this->createForm(ContactoFormType::class, $contacto);
    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()) {
        $contacto = $formulario->getData();
        $entityManager = $doctrine->getManager();
        $entityManager->persist($contacto);
        $entityManager->flush();
        return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
    }
    return $this->render('nuevo.html.twig', array('formulario' => $formulario->createView()));
}
#[Route('/contacto/editar/{codigo}', name: 'editar', requirements:["codigo"=>"\d+"])]

public function editar(ManagerRegistry $doctrine, Request $request, int $codigo) {
    $repositorio = $doctrine->getRepository(Contacto::class);
    //En este caso, los datos los obtenemos del repositorio de contactos
    $contacto = $repositorio->find($codigo);
    if ($contacto){
        // A partir de $contacto, rellena automáticamente el formulario y el resto es igual que para nuevo
        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Guardamos y redirigimos a la ficha
            $contacto = $formulario->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);

        }
        // Ponemos los datos del contacto

        return $this->render('editar.html.twig', array(

            'formulario' => $formulario->createView()

        ));
    }else{
        return $this->render('ficha_contacto.html.twig', [

            'contacto' => NULL
        ]);
    }
}
}