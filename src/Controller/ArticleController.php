<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 03.01.2019
 * Time: 13:04
 */

namespace App\Controller;


use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PhpParser\JsonDecoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ArticleController extends Controller
{
    /**
     * @Route("/")
     */
    public function homepage()
    {
        $tag = $this->getUsersData();
        var_dump($tag);
//        return new Response($tag[0]);
    }

    /**
     * @Route("show/{tag}")
     */
    public function show($tag)
    {

        return new Response(sprintf('This is method show: %s', $tag));
    }

    private function setUsersData(int $count)
    {
        $faker = Factory::create('ru_RU');
        $em  = $this->getDoctrine()->getManager();

        for ($i = 1; $i <= $count; $i++){
            $modelUsers = new Users();
            $modelUsers->setName($faker->name);
            $modelUsers->setAge(rand(1990,2018));
            $em->persist($modelUsers);
            $em->flush();
        }
    }

    /**
     * @Route("/test")
     */
    public function test()
    {
        $users = $this->getUsersData();
        if (empty($users)){
            $this->setUsersData(12);
            $users = $this->getUsersData();
        }
        return var_dump($users);
    }

    public function getUsersData()
    {
        $repository = $this->getDoctrine()->getRepository(Users::class);
        $products = $repository->findAll();

        return $products;
    }

    /**
     * @Route("/rest_api/index", name="article-rest-api", methods={"POST"})
     */
    public function restApiIndex(): JsonResponse
    {
        $array = [];
        $users = $this->getUsersData();
        if (empty($users)){
            $this->setUsersData(12);
            $users = $this->getUsersData();
        }
        foreach ($users as $arr){
            array_push($array, [$arr->getId(), $arr->getName(), $arr->getAge()]);
        }

        return new JsonResponse($array);
    }

    /**
     * @Route("/rest_api/update", methods={"POST"})
     * @return JsonResponse
     */
    public function restApiUpdate()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!is_null($data)){
            $array = array_values((array)$data);
            $em = $this->getDoctrine()->getManager();
            $modelUser = $em->getRepository(Users::class)->find((int)$array[0]);
            $username = strip_tags($array[1]);
            $modelUser->setName($username);
            $em->flush();

            return new JsonResponse('Yes!!!');
        }

        throw new HttpException(400, "Invalid data");

    }
}