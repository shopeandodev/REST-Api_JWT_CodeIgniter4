<?php

namespace App\Controllers\API;

use App\Models\UserModel;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Exception;
use ReflectionException;

class User extends BaseController
{
    // use ResponseTrait;
    /**
     * Register a new user
     * @return Response
     * @throws ReflectionException
     */
    public function register()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[user.email]',
            'password' => 'required|min_length[6]|max_length[255]'
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                        $this->validator->getErrors(),
                        ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        $userModel = new UserModel();
        $userModel->save($input);

        return $this->getJWTForUser(
                $input['email'],
                ResponseInterface::HTTP_CREATED
            );
    }

    /**
     * Authenticate Existing User
     * @return Response
     */
    public function login()
    {
        $rules = [
            'email' => 'required|min_length[6]|max_length[50]|valid_email',
            'password' => 'required|min_length[6]|max_length[255]|validateUser[email, password]'
        ];

        $errors = [
            'password' => [
                'validateUser' => 'Invalid login credentials provided'
            ]
        ];

        $input = $this->getRequestInput($this->request);


        if (!$this->validateRequest($input, $rules, $errors)) {
            return $this->getResponse(
                    $this->validator->getErrors(),
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }
        return $this->getJWTForUser($input['email']);
    }

    private function getJWTForUser(string $emailAddress, int $responseCode = ResponseInterface::HTTP_OK) 
    {
        try {
            $model = new UserModel();
            $user = $model->findUserByEmailAddress($emailAddress);
            unset($user['password']);

            helper('jwt');

            return $this->getResponse(
                    [
                        'message' => 'User authenticated successfully',
                        'user' => $user,
                        'access_token' => getSignedJWTForUser($emailAddress)
                    ]
                );
        } catch (Exception $ex) {
            return $this->getResponse(
                    [
                        'error' => $ex->getMessage(),
                    ],
                    $responseCode
                );
        }
    }

    public function instantWin()
    {
        /* GET PARAMETERS */
        $parameters= [];
        foreach($this->request->getGet() as $key => $value) {
            $parameters[$key] = $value;            
        }

        $longitudDeseada = 6; // Define la longitud del string deseado

        return $this->getResponse(
            [
                'message' => 'Instant win awarded',
                'code' => $this->generarStringAlfanumerico(6) . '-' . $this->generarStringAlfanumerico(6) . '-' . $this->generarStringAlfanumerico(6) 
            ]
        );
    }

    private function generarStringAlfanumerico($longitud) {
        // Caracteres alfanuméricos en mayúsculas
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        // Longitud de la cadena de caracteres
        $numeroCaracteres = strlen($caracteres);
        
        // String aleatorio
        $stringAleatorio = '';
        
        // Generar el string aleatorio
        for ($i = 0; $i < $longitud; $i++) {
            $stringAleatorio .= $caracteres[rand(0, $numeroCaracteres - 1)];
        }
        
        return $stringAleatorio;
    }
}
