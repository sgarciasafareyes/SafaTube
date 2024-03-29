<?php

namespace App\Controller;

use App\Entity\Canal;
use App\Entity\ListaReproduccion;
use App\Entity\TipoCategoria;
use App\Entity\TipoPrivacidad;
use App\Entity\Video;
use App\Repository\ListaReproduccionRepository;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/listaReproduccion')]

class ListaReproduccionController extends AbstractController
{
    #[Route('/listar', name: 'listar_listasReproduccion')]
    public function list(ListaReproduccionRepository $listaReproduccionRepository): JsonResponse
    {
        $listasReproduccion = $listaReproduccionRepository->findAll();

        return $this->json(['message' => $listasReproduccion], Response::HTTP_CREATED);
    }

    #[Route('/get', name: 'lista_by_id', methods: ['POST'])]
    public function getById(EntityManagerInterface $entityManager,Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $listaReproduccion = $entityManager->getRepository(ListaReproduccion::class)->findBy(["id"=> $data]);
        return $this->json($listaReproduccion[0]);
    }

    #[Route('/crear', name: 'crear_lista', methods: ['POST'])]
    public function crear(EntityManagerInterface $entityManager, Request $request,ListaReproduccionRepository $listaReproduccionRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $listaReproduccionNueva = new ListaReproduccion();
        $listaReproduccionNueva->setNombre($data['nombre']);

        $canal = $entityManager->getRepository(Canal::class)->findBy(["id"=> $data["canal"]["id"]]);
        $listaReproduccionNueva->setCanal($canal[0]);

//        $listaReproduccionNueva->addVideos(Video::class);
        $entityManager->persist($listaReproduccionNueva);
        $entityManager->flush();

        return $this->json(['message' => 'Lista creada correctamente'], Response::HTTP_CREATED);
    }

    #[Route('/editar/{id}', name: 'editar_lista', methods: ['PUT'])]
    public function editar(EntityManagerInterface $entityManager, Request $request,ListaReproduccion $listaReproduccion): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $listaReproduccion->setNombre($data['nombre']);

        $entityManager->flush();

        return $this->json(['message' => 'Lista modificada'], Response::HTTP_CREATED);
    }

    #[Route('/eliminar/{id}', name: "borrar_lista", methods: ["DELETE"])]
    public function deleteById(EntityManagerInterface $entityManager, ListaReproduccion $listaReproduccion):JsonResponse
    {
        $entityManager->remove($listaReproduccion);
        $entityManager->flush();

        return $this->json(['message' => 'Lista eliminada'], Response::HTTP_OK);
    }

    #[Route('/getListas', name: 'listas_by_idCanal', methods: ['POST'])]
    public function getByIdCanal(EntityManagerInterface $entityManager,Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $listasReproduccion = $entityManager->getRepository(ListaReproduccion::class)->getListas(["id"=> $data]);
        return $this->json($listasReproduccion);
    }

    #[Route('/getVideosListas', name: 'get_videos_listas', methods: ['POST'])]
    public function getVideosListas(EntityManagerInterface $entityManager,Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $videos = $entityManager->getRepository(Video::class)->getVideosListas(["id"=> $data]);
        return $this->json(['video' =>$videos]);
    }

    #[Route('/agregarVideo', name: 'añadir_video', methods: ['POST'])]
    public function anyadirVideo(EntityManagerInterface $entityManager,Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
//        $listaReproduccion = $entityManager->getRepository(ListaReproduccion::class)->findBy(["id"=> $data["lista"]["id"]]);
//
////        $canal = $entityManager->getRepository(Canal::class)->findBy(["id"=> $data["lista"]["id_canal"]]);
////        $listaReproduccion->setCanal($canal);
//
//        $video = $entityManager->getRepository(Video::class)->findBy(["id"=> $data["video"]["id"]]);
        $agregarVideo = $entityManager->getRepository(ListaReproduccion::class)->anyadirVideo(["id"=>$data]);



        return $this->json('ok', Response::HTTP_OK);
    }

}
