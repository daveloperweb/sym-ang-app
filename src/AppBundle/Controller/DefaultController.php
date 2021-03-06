<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use Symfony\Component\Validator\Constraints as Assert;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    public function loginAction(Request $request){
        $helpers = $this->get(Helpers::class);

        // Recibir json por post
        $json = $request->get('json', null);

        //array a devolver por defecto
        $data= array(
                'status'=> 'error',
                'data' => 'send json via post !!'
        );

        if ($json != null){

            //convertimos json en objeto php
            $params = json_decode($json);

            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "Email no válido";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);

            //Cifrar la clave
            $pwd = hash('sha256', $password);

            if ($email != null && count($validate_email) == 0 && $password != null){

                $jwt_auth = $this->get(JwtAuth::class); //aqui en $jwt_auth, guardas = , esto $this->, y se lo pones get(JwtAuth::class)

                if ($getHash == null || $getHash == false){
                    $signup = $jwt_auth->signup($email, $pwd);
                }else{
                    $signup = $jwt_auth->signup($email, $pwd, true);
                }

                return $this->json($signup);

            }else{
                $data= array(
                    'status'=> 'error',
                    'data' => 'email or password incorrect'
                );
            }
        }
        return $helpers->json($data);
    }


    public function pruebasAction(Request $request){

        $token = $request->get("authorization", null);
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        if($token && $jwt_auth->checkToken($token) == true){

            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository('BackendBundle:User');
            $users = $userRepo->findAll();

            return $helpers->json(array(
                    'status'=> 'success',
                    'users'=> $users
            ));
        }else{
            return $helpers->json(array(
                    'status'=> 'error',
                    'code'=> 400,
                    'data'=> "Login failed!!"
            ));
        }
        /*
        die();

        */
    }
}
