<?php

namespace App\Controller;

use App\Entity\Canal;
use App\Entity\Suscripcion;
use App\Entity\TipoContenido;
use App\Entity\Usuario;
use App\Repository\CanalRepository;
use App\Repository\MensajeRepository;
use App\Repository\NotificacionRepository;
use App\Repository\TipoCategoriaRepository;
use App\Repository\TipoContenidoRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/canal')]
class CanalController extends AbstractController
{

    #[Route('/listar', name: "canal_list", methods: ["GET"])]
    public function list(CanalRepository $canalRepository):JsonResponse
    {
        $list = $canalRepository->findAll();

        return $this->json($list);
    }

    #[Route('/listarsubs', name: "canal_subslist", methods: ["POST"])]
    public function listarsubs(NotificacionRepository $notificacionRepository,EntityManagerInterface $entityManager, CanalRepository $canalRepository, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $usuarios = $entityManager->getRepository(Usuario::class)->findBy(["username"=>$data['username']]);
        $usuario = $usuarios[0];
        $canales = $canalRepository->getSubs($usuario->getId());
        $lista = [];
        foreach ($canales as $c){
            $esfera = false;
            $notis = $notificacionRepository->getNotiSuscripciones($c['id']);
            if (count($notis)>0){
                $esfera = true;
            }
            $elemento = ["canal"=>$c,"esfera"=>$esfera];
            array_push($lista,$elemento);
        }
        return $this->json($lista);
    }

    #[Route('/verSuscriptores', name: "ver_suscriptores", methods: ["POST"])]
    public function verSuscriptores(EntityManagerInterface $entityManager, CanalRepository $canalRepository, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $canal = $entityManager->getRepository(Canal::class)->findBy(["id"=>$data['id']]);

        $suscriptores = $canalRepository->suscriptoresCanal($canal[0]);

        return $this->json($suscriptores);
    }

    #[Route('/get', name: "canal_usuario_log", methods: ["POST"])]
    public function get(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $canalData = $entityManager->getRepository(Canal::class)->getByUsuarioLogueado($data);
        $canalUsuarioLogeado = $entityManager->getRepository(Canal::class)->find($canalData[0]["id"]);

        return $this->json($canalUsuarioLogeado);
    }

    #[Route('/getVideosSegunCanal', name: "get_videos_segun_canal", methods: ["POST"])]
    public function getVideosSegunCanal(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $videos = $entityManager->getRepository(Canal::class)->getVideosSegunCanal($data);

        return $this->json($videos);
    }

    #[Route('/getCanalSegunUsername', name: "get_canal_segun_username", methods: ["POST"])]
    public function getCanalSegunUsername(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $usuario = $entityManager->getRepository(Usuario::class)->findBy(["username" => $data["usuario"]]);
        $canal = $entityManager->getRepository(Canal::class)->findBy(["usuario" => $usuario[0]->getId()]);

        return $this->json($canal[0]);
    }

    #[Route('/listartTipoContenido', name: 'listar_tipo_contenido', methods: ['GET'])]
    public function listartTipoContenido(TipoContenidoRepository $tipoContenidoRepository): JsonResponse
    {
        $contenidos = $tipoContenidoRepository->findAll();

        return $this->json($contenidos);
    }
    #[Route('/getVideosPopularesSegunCanal', name: "get_videos_populares_segun_canal", methods: ["POST"])]
    public function getVideosPopularesSegunCanal(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $videos = $entityManager->getRepository(Canal::class)->getVideosPopularesSegunCanal($data);

        return $this->json($videos);
    }
    #[Route('/getVideosSoloSubs', name: "get_videos_solo_subs", methods: ["POST"])]
    public function getVideosSoloSubs(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $videos = $entityManager->getRepository(Canal::class)->getVideosSoloSubs($data);

        return $this->json($videos);
    }

    #[Route('/getInfoCanal', name: "get_info_canal", methods: ["POST"])]
    public function getInfoCanal(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $data = json_decode($request-> getContent(), true);
        $numeroVideos = $entityManager->getRepository(Canal::class)->getNumeroVideosSubidos($data);
        $numeroSuscriptores = $entityManager->getRepository(Canal::class)->getSuscripcionesTotales($data);
        $numeroVisitas = $entityManager->getRepository(Canal::class)->getVisitasTotales($data);

        return $this->json(['numeroVideos' => $numeroVideos, 'numeroSuscriptores' => $numeroSuscriptores, 'numeroVisitas' => $numeroVisitas]);
    }

    #[Route('/get/{id}', name: "canal_by_id", methods: ["GET"])]
    public function getById(Canal $canal):JsonResponse
    {
        return $this->json($canal);
    }

    #[Route('/crear', name: "crear_canal", methods: ["POST"])]
    public function crear(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $json = json_decode($request-> getContent(), true);

        $nuevoCanal = new Canal();
        $nuevoCanal->setDescripcion($json["descripcion"]);
        $nuevoCanal->setNombre($json["nombre"]);
        $nuevoCanal->setApellidos($json["apellidos"]);
//        $nuevoCanal->setEmail($json["email"]);
        $nuevoCanal->setFechaNacimiento($json["fecha_nacimiento"]);
        $nuevoCanal->setTelefono($json["telefono"]);
        $nuevoCanal->setFoto($json["foto"]);
        $nuevoCanal->setTipoContenido($json["tipo_contenido"]);
        $nuevoCanal->setBanner($json["banner"]);

        $usuario = $entityManager->getRepository(Usuario::class)->findBy(["id"=> $json["usuario"]]);
        $nuevoCanal->setUsuario($usuario[0]);

        $entityManager->persist($nuevoCanal);
        $entityManager->flush();

        return $this->json(['message' => 'Canal creado'], Response::HTTP_CREATED);
    }


    #[Route('/editar/{id}', name: "editar_canal", methods: ["PUT"])]
    public function editar(EntityManagerInterface $entityManager, Request $request, Canal $canal):JsonResponse
    {
        $json = json_decode($request-> getContent(), true);

        $canal->setDescripcion($json["descripcion"]);
        $canal->setNombre($json["nombre"]);
        $canal->setApellidos($json["apellidos"]);
        $canal->setFechaNacimiento($json["fechaNacimiento"]);
        $canal->setTelefono($json["telefono"]);
        $canal->setFoto($json["foto"]);
        $canal->setBanner($json["banner"]);

        $usuario = $entityManager->getRepository(Usuario::class)->findBy(["id"=> $json["usuario"]["id"]]);
        $canal->setUsuario($usuario[0]);

        $tipoContenido = $entityManager->getRepository(TipoContenido::class)->findBy(["nombre"=> $json["tipoContenido"]]);
        $canal->setTipoContenido($tipoContenido[0]);

        $entityManager->flush();

        return $this->json(['message' => 'Canal modificado'], Response::HTTP_OK);
    }

    #[Route('/eliminar/{id}', name: "delete_by_id", methods: ["DELETE"])]
    public function deleteById(EntityManagerInterface $entityManager, Canal $canal):JsonResponse
    {
        $entityManager->remove($canal);
        $entityManager->flush();

        return $this->json(['message' => 'Canal eliminado'], Response::HTTP_OK);
    }

    #[Route('/buscar', name: "buscar_canal", methods: ["POST"])]
    public function findCanal(EntityManagerInterface $entityManager, Request $request):JsonResponse
    {
        $data = $request->getContent();
        $listaCanales = $entityManager->getRepository(Canal::class)->findCanales(["nombre"=> $data]);

        return $this->json(['canales' => $listaCanales], Response::HTTP_OK);
    }




}
