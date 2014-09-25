<?php

namespace multikanban\multikanban\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use multikanban\multikanban\Model\User;
use multikanban\multikanban\Repository\UserRepository;
use multikanban\multikanban\Api\ApiProblemException;
use multikanban\multikanban\Api\ApiProblem;

class UserController extends BaseController{

    protected function addRoutes(ControllerCollection $controllers){

    	$controllers->post('/users', array($this, 'createAction'));
        $controllers->get('/users', array($this, 'getAllAction'));
        $controllers->get('/users/{id}', array($this, 'getAction'));
        $controllers->put('/users/{id}', array($this, 'updateAction'));
        $controllers->delete('/users/{id}', array($this, 'deleteAction'));
    }

    public function createAction(Request $request){

    	$data = json_decode($request->getContent(), true);

        // Check invalid json error
        $this->checkInvalidJSON($data);

    	$user = new User();
    	$user->username = $data['username'];
    	$user->setPlainPassword($data['password']);
    	$user->email = $data['email'];
        $user->registered = date("Y-m-d");

        // Check validation error
        $this->checkValidation($user);

    	$this->getUserRepository()->save($user);

        $newUser = $this->getUserRepository()->findOneByUsername($user->username);

        $userArray = array(
            'id' => $newUser->id,
            'username' => $newUser->username,
            'email' => $newUser->email,
            'registered' => $newUser->registered
        );

        return new JsonResponse($userArray, 201);
    }

    public function getAllAction(){

        $users = $this->getUserRepository()->findAll();

        $data = array();

        foreach ($users as $eachUser) {
            $eachArray = array();
            foreach ($eachUser as $key => $value) {
               $eachArray[$key] = $value;
            }
            array_push($data, $eachArray);
        }

        return new JsonResponse($data, 200);
    }

    public function getAction($id){

        $user = $this->getUserRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($user);

        $data = array(
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'registered' => $user->registered
        );

        return new JsonResponse($data, 200);
    }

    public function updateAction(Request $request, $id){

        $user = $this->getUserRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($user);

        $data = json_decode($request->getContent(), true);

        // Check invalid json error
        $this->checkInvalidJSON($data);

        $emailChanged = false;
        //username can't be changed
        //$user->username = $data['username'];
        $user->setPlainPassword($data['password']);
        if($user->email != $data['email']){
            $user->email = $data['email'];
            $emailChanged = true;
        }

        // Check validation error
        $this->checkValidation($user);

        $this->getUserRepository()->update($user, $emailChanged);

        // $newUser = $this->getUserRepository()->findOneByUsername($user->username);

        $userArray = array(
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'registered' => $user->registered
        );

        return new JsonResponse($userArray, 200);
    }

    public function deleteAction(Request $request, $id){

        $user = $this->getUserRepository()->findOneById($id);

        // Check not found error
        $this->checkNotFound($user);

        $this->getUserRepository()->delete($user);

        return new Response(null, 204);
    }
}
