<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Form\ContactoFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{

    // Si queremos validar un parámetro, su usa 'requeriments' que es una expresión regular. En este caso, solo permite números de longitud variable

#[Route('/contacto/{codigo}', name: 'contacto', requirements: ['codigo' => '[0-9]+'], methods: ['GET', 'POST'])]

// Symfony inyecta la dependencia ManagerRegistry automáticamente

// Le pasa la variable $codigo con el valor en {codigo}. Si no se le pasa, coge 1 por defecto, en otro caso, daría not found

public function ficha(ManagerRegistry $doctrine, Request $request, int $codigo = 1): Response
{
    // La primera instrucción suele ser esta, ya que cogemos el repositorio de la entidad asociada

    if(!$this->getUser()){
        return $this->redirectToRoute('inicio');
    }

    $repositorio = $doctrine->getRepository(Contacto::class);

    // Ahora usamos uno de los métodos del repositorio

    $contacto = $repositorio->find($codigo);

    // Y creamos los botones editar y borrar
    
        if ($request->isMethod('POST')) {
            $accion = $request->request->get("accion"); 

            if ($accion === 'editar') {
                return $this->redirectToRoute('editar', ["codigo" => $codigo]);
            }

            if ($accion === 'borrar' && $contacto) {
                $entityManager = $doctrine->getManager();
                $entityManager->remove($contacto);
                $entityManager->flush();
                return $this->redirectToRoute('inicio');
            }
        }
 
    
// Y creamos la vista HTML
 

   return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);
}

#[Route('/contacto/nuevo/{nombre}/{telefono}/{email}', name: 'nuevo-con-datos')]

// Symfony inyecta la dependencia ManagerRegistry automáticamente

// Le pasa la variable $codigo con el valor en {codigo}. Si no se le pasa, coge 1 por defecto, en otro caso, daría not found

public function nuevoConDatos(ManagerRegistry $doctrine, String $nombre, String $telefono, String $email)
{

    //Inicializamos el objeto
$contacto = new Contacto();

//Setters
$contacto->setNombre($nombre);
$contacto->setTelefono($telefono);
$contacto->setEmail($email);

//Guardamos el objeto
$entityManager = $doctrine->getManager();
$entityManager->persist($contacto);
$entityManager->flush();

   return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
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



        if ($request->isMethod('POST') && $request->request->get('accion') === 'borrar') {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($contacto);
            $entityManager->flush();

            return $this->redirectToRoute('inicio');
        }

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

            'formulario' => $formulario->createView(),
            'contacto' => $contacto

        ));

    }else{

        return $this->render('ficha_contacto.html.twig', [

            'contacto' => NULL

        ]);

    }

}



}