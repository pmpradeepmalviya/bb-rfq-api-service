<?php
namespace App\Http\Services\api;
use Aws\SecretsManager\SecretsManagerClient;

class AwsService{

    public function getSecret(){

        $response = [
            'error_code' => null,
        ];

        $secret_manager_data = $this->secretManagerClientData();
        try {
            $result = $secret_manager_data['client']->getSecretValue([
                'SecretId' => $secret_manager_data['secretName'],
            ]);
            
            // Depending on whether the secret is a string or binary, one of these fields will be populated.
            if (isset($result['SecretString'])) {
                $secret = $result['SecretString'];
            } else {
                $secret = base64_decode($result['SecretBinary']);
            }

            $secretArray = json_decode($secret, true);

            $response = [
                'error_code' => null,
                'data' => $secretArray,
            ];
            \Log::channel('response')->info(["secretManagerClientData"=> $response]);
            return $response;
            
        } catch (AwsException $e) {
            if($e->getMessage() != ""){
                return response([
                    'error_code' => 500,
                    'status'     => false,
                    'message'    => $e->getMessage(),
                ],500)->header('Content-Type','application/json');
            }
        }
    }

    public function setSecret($new_password,$secret_data){
        try {

            $response = [
                'error_code' => null,
            ];

            $secret_manager_data = $this->secretManagerClientData();
            $secret = json_encode([
                "member" => $secret_data['member'],
                "loginId" => $secret_data['loginId'],
                "password" => $new_password,
                "url" => $secret_data['url'],
            ]);
        
                $result = $secret_manager_data['client']->putSecretValue([
                    'SecretId' => $secret_manager_data['secretName'],
                    'SecretString' => $secret,
                ]);

                $response = [
                    'error_code' => null,
                    'data' => $result,
                    'status' => 'success',
                ];
                
                return $response;
        } catch (AwsException $e) {
            if($e->getMessage() != ""){
                return response([
                    'error_code' => 500,
                    'status'     => false,
                    'message'    => $e->getMessage(),
                ],500)->header('Content-Type','application/json');
            }
        }

    }

    public function secretManagerClientData(){
            $client = new SecretsManagerClient([
                'version' => 'latest',
                'region' => config('constant.AWS_DEFAULT_REGION'),
            ]);
            $secretName = 'bse-rfq-api-uat';
            return ['client' => $client,'secretName' => $secretName];
        
    }

}
