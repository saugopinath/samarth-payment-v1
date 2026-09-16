<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\ApiResponseLog;
use App\Models\ApiErrorLog;
use App\Models\ApiStepLog;
use Illuminate\Support\Facades\Crypt;

class IfmsApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            // 'verify' => false, // Uncomment if SSL verification needs to be bypassed locally
        ]);
    }

    // -------------------------------------------------------------------------
    // PUBLIC API METHODS
    // -------------------------------------------------------------------------

    public function authenticate($client_id, $client_secret)
    {
      

        try {
            $postUrl = config('services.ifms.base_url') . 'food/main/version/authenticate';
            $publicKeyPath = storage_path(config('services.ifms.public_key_path', 'app/IFMS/publicKey.pem'));
            
            $symmetricKey = $this->generateAES256Key();
            $encryptedAppKey = $this->encryptSymmetricKey($symmetricKey, $publicKeyPath);

            $headers = [
                'Content-Type' => 'application/json',
                'clientId'     => $client_id,
                'clientSecret' => $client_secret,
            ];
            
            $payload = [
                'appKey' => base64_encode($encryptedAppKey),
            ];

            $response = $this->client->post($postUrl, [
                'headers' => $headers,
                'json' => $payload
            ]);

            $responseBody = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();
            
            ApiResponseLog::create([
                'api_name'      => $postUrl,
                'request_data'  => json_encode($payload),
                'http_status'   => $statusCode,
                'response_data' => $responseBody,
                'ip_address'    => request()->ip(),
                'user_agent'    => request()->userAgent()
            ]);

            $responseData = json_decode($responseBody, true);
            if (!isset($responseData['status']) || !$responseData['status']) {
                throw new \Exception('Authentication failed: ' . ($responseData['errorMessage'] ?? 'Unknown error'));
            }
            
            $authToken = $responseData['data']['authToken'] ?? null;
            $sek = $responseData['data']['sek'] ?? null;
            
            if (!$authToken || !$sek) {
                throw new \Exception('Missing authToken or SEK in response');
            }
            
            $decryptedSek = $this->decryptAES256ECB($sek, $symmetricKey, true);

            Cache::put('IFMS_AUTH', Crypt::encrypt([
                'client_id'       => $client_id,
                'IFMS_authToken'  => $authToken,
                'IFMS_sek'        => $decryptedSek,
                'IFMS_sek_raw'    => $sek,
                'IFMS_appKey_raw' => $symmetricKey,
            ]), Carbon::now()->addMinutes(30));

            return true;
        } catch (\Exception $e) {
            $this->logApiError($e, $postUrl ?? 'unknown', $payload ?? null);
            return false;
        }
    }

    public function billSharing($payload, $lot_no, $DRNNo, $client_id, $client_secret)
    {
        

        try {
            $post_url = config('services.ifms.base_url') . 'food/main/version/bill-details';
            
            $responseArray = $this->sendEncryptedPostRequest(
                $post_url,
                $payload,
                $client_id,
                $client_secret,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            dd($responseArray);

            if (isset($responseArray['status']) && $responseArray['status'] == true && isset($responseArray['data']) && isset($responseArray['rek']) && isset($responseArray['hmac'])) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logApiError($e, $post_url ?? 'unknown', $payload ?? null);
            return false;
        }
    }

    public function BeneficiaryShared($jsonData, $client_id, $drn_no, $lot_no, $client_secret)
    {
        if (config('app.env') === 'local') {
            return [
                'status' => 1,
                'drn' => $drn_no ?? 'MOCK_DRN_' . time(),
                'statusMsg' => 'Success',
                'beneficiary' => []
            ];
        }

        try {
            ApiStepLog::create([
                'user_id'    => auth()->id(),
                'step_name'  => 'BeneficiaryShared',
                'lot_no'     => $lot_no,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $post_url = config('services.ifms.base_url') . 'food/main/version/beneficiary-details';
            
            $responseBody = $this->sendEncryptedPostRequestRaw(
                $post_url,
                $jsonData,
                $client_id,
                $client_secret
            );
            
            $responseData = json_decode($responseBody, true);
            $status = $responseData['status'] ?? null;
            $rek = $responseData['rek'] ?? null;
            $data = $responseData['data'] ?? null;
            $drn = $responseData['drn'] ?? null;
            
            $return_arr = [
                'status' => null,
                'drn' => null,
                'statusMsg' => null,
                'beneficiary' => null
            ];

            if ($status == true && $rek && $data) {
                $responseArray = $this->decryptIfmsResponse($rek, $data);
                
                $return_arr['status'] = 1;
                $return_arr['drn'] = $responseArray['drn'] ?? null;
                $return_arr['statusMsg'] = $responseArray['statusMsg'] ?? null;
                $return_arr['beneficiary'] = $responseArray['beneficiary'] ?? null;
            } else if ($status === null && $drn) {
                $return_arr['status'] = 2;
                $return_arr['statusMsg'] = $responseData['statusMsg'] ?? null;
                $return_arr['beneficiary'] = $responseData['beneficiary'] ?? null;
            }

            return $return_arr;
        } catch (\Exception $e) {
            $this->logApiError($e, $post_url ?? 'unknown', $jsonData ?? null);
            return false;
        }
    }

    public function billGenerated($drn_no, $client_id, $lot_no, $client_secret)
    {
        if (config('app.env') === 'local') {
            return [
                'status' => 1,
                'referenceNumber' => 'REF' . time(),
                'billStatus' => 'SUCCESS',
                'reason' => 'Processed Successfully'
            ];
        }

        try {
            ApiStepLog::create([
                'user_id'    => auth()->id(),
                'step_name'  => 'billGenerated',
                'lot_no'     => $lot_no,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $post_url = config('services.ifms.base_url') . 'food/main/version/get-bill-status';
            $payloadObj = ["drn" => $drn_no];
            
            $responseBody = $this->sendEncryptedPostRequestRaw(
                $post_url,
                $payloadObj,
                $client_id,
                $client_secret,
                JSON_UNESCAPED_SLASHES
            );
            
            $responseData = json_decode($responseBody, true);
            $status = $responseData['status'] ?? null;
            $rek = $responseData['rek'] ?? null;
            $data = $responseData['data'] ?? null;
            
            $return_arr = [
                'status' => null,
                'referenceNumber' => null,
                'billStatus' => null,
                'reason' => null
            ];

            if ($status == true && $rek && $data) {
                $responseArray = $this->decryptIfmsResponse($rek, $data);
                
                $return_arr['status'] = 1;
                $return_arr['referenceNumber'] = $responseArray['referenceNumber'] ?? null;
                $return_arr['billStatus'] = $responseArray['billStatus'] ?? null;
                $return_arr['reason'] = $responseArray['reason'] ?? null;
            }

            return $return_arr;
        } catch (\Exception $e) {
            $this->logApiError($e, $post_url ?? 'unknown', $payloadObj ?? null);
            return false;
        }
    }

    public function BeneficiaryResponse($client_id, $drn_no, $client_secret, $lot_no)
    {
        if (config('app.env') === 'local') {
            return [
                'status' => 1,
                'payment_details' => ['amount' => 1000, 'date' => date('Y-m-d')],
                'beneficiary_details' => ['name' => 'Mock Beneficiary', 'status' => 'Paid']
            ];
        }

        try {
            ApiStepLog::create([
                'user_id'    => auth()->id(),
                'step_name'  => 'BeneficiaryResponse',
                'lot_no'     => $lot_no,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $post_url = config('services.ifms.base_url') . 'food/main/version/payment-success-failure-info';
            $jsonPayloadObj = ["drn" => $drn_no];
            
            $responseBody = $this->sendEncryptedPostRequestRaw(
                $post_url,
                $jsonPayloadObj,
                $client_id,
                $client_secret,
                JSON_UNESCAPED_SLASHES
            );
            
            $responseData = json_decode($responseBody, true);
            $status = $responseData['status'] ?? null;
            $data = $responseData['data'] ?? null;
            $rek = $responseData['rek'] ?? null;
            
            $return_arr = [
                'status' => null,
                'payment_details' => null,
                'beneficiary_details' => null
            ];

            if ($status == true && $rek && $data) {
                $responseArray = $this->decryptIfmsResponse($rek, $data);
                
                $return_arr['status'] = 1;
                $return_arr['payment_details'] = $responseArray['paymentDetails'] ?? null;
                $return_arr['beneficiary_details'] = $responseArray['beneficiaryDetails'] ?? null;
            }

            return $return_arr;
        } catch (\Exception $e) {
            $this->logApiError($e, $post_url ?? 'unknown', $jsonPayloadObj ?? null);
            return false;
        }
    }

    public function Allotement_data($client_id, $hoa_arr, $client_secret)
    {
        if (config('app.env') === 'local') {
            return [
                'status' => 1,
                'headofAccount' => $hoa_arr['headofAccount'] ?? 'MOCK-HOA',
                'progressiveExp' => 50000,
                'ddoBalance' => 100000,
                'treasuryBalance' => 500000
            ];
        }

        try {
            $post_url = config('services.ifms.base_url') . 'food/main/version/hoa-balance';
            
            $responseBody = $this->sendEncryptedPostRequestRaw(
                $post_url,
                $hoa_arr,
                $client_id,
                $client_secret,
                JSON_UNESCAPED_SLASHES
            );
            
            $responseData = json_decode($responseBody, true);
            $status = $responseData['status'] ?? null;
            $data = $responseData['data'] ?? null;
            $rek = $responseData['rek'] ?? null;
            
            $return_arr = [
                'status' => null,
                'headofAccount' => null,
                'progressiveExp' => null,
                'ddoBalance' => null,
                'treasuryBalance' => null
            ];

            if ($status == true && $rek && $data) {
                $responseArray = $this->decryptIfmsResponse($rek, $data);
                
                $return_arr['status'] = 1;
                $return_arr['headofAccount'] = $responseArray['headofAccount'] ?? null;
                $return_arr['progressiveExp'] = $responseArray['progressiveExp'] ?? null;
                $return_arr['ddoBalance'] = $responseArray['ddoBalance'] ?? null;
                $return_arr['treasuryBalance'] = $responseArray['treasuryBalance'] ?? null;
            }

            return $return_arr;
        } catch (\Exception $e) {
            $this->logApiError($e, $post_url ?? 'unknown', $hoa_arr ?? null);
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------------------------

    private function ensureAuthenticated($client_id, $client_secret)
    {
        $ifmsCache = $this->getIfmsAuthCache();
        if (
            empty($ifmsCache) ||
            empty($ifmsCache['IFMS_authToken']) ||
            empty($ifmsCache['IFMS_sek']) ||
            $ifmsCache['client_id'] !== $client_id
        ) {
            $authenticated = $this->authenticate($client_id, $client_secret);
            if (!$authenticated) {
                Cache::forget('IFMS_AUTH');
                $authenticated = $this->authenticate($client_id, $client_secret);
            }
            if (!$authenticated) {
                throw new \Exception("Unable to authenticate with IFMS.");
            }
            $ifmsCache = $this->getIfmsAuthCache();
        }
        
        return $ifmsCache;
    }

    private function sendEncryptedPostRequestRaw($endpoint, $payloadArray, $client_id, $client_secret, $jsonFlags = 0)
    {
        $ifmsCache = $this->ensureAuthenticated($client_id, $client_secret);
        
        $authToken = $ifmsCache['IFMS_authToken'];
        $sek = $ifmsCache['IFMS_sek'];
        
        $jsonPayload = json_encode($payloadArray, $jsonFlags);
        if ($jsonFlags === (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) {
            $jsonPayload = trim($jsonPayload);
            $jsonPayload = preg_replace('/\x{FEFF}/u', '', $jsonPayload);
        }

        $encryptedData = $this->encryptAES256Base64($jsonPayload, $sek);
        $hmac = $this->generateHmacBase64($jsonPayload, $sek);
        
        $requestPayload = [
            'hmac' => $hmac,
            'data' => $encryptedData
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'clientId'     => $client_id,
            'authToken'    => $authToken,
        ];

        $response = $this->client->post($endpoint, [
            'headers' => $headers,
            'json' => $requestPayload
        ]);

        $responseBody = $response->getBody()->getContents();
        $statusCode = $response->getStatusCode();
        
        ApiResponseLog::create([
            'api_name'      => $endpoint,
            'request_data'  => json_encode($payloadArray),
            'http_status'   => $statusCode,
            'response_data' => $responseBody,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent()
        ]);

        if ($statusCode !== 200) {
            throw new \Exception('IFMS HTTP ERROR: ' . $statusCode);
        }

        return $responseBody;
    }

    private function sendEncryptedPostRequest($endpoint, $payloadArray, $client_id, $client_secret, $jsonFlags = 0)
    {
        $responseBody = $this->sendEncryptedPostRequestRaw($endpoint, $payloadArray, $client_id, $client_secret, $jsonFlags);
        return json_decode($responseBody, true);
    }

    private function decryptIfmsResponse($rek, $data)
    {
        $ifmsCache = $this->getIfmsAuthCache();
        $sek = $ifmsCache['IFMS_sek'];

        $sek_bytes = str_pad(base64_decode($sek, true), 32, "\0");
        
        // Decrypt REK with SEK
        $decryptedrek = $this->decryptAES256ECB($rek, $sek_bytes, true);
        $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");
        
        // Decrypt DATA with REK
        $decryptedData = $this->decryptAES256ECB($data, $rek_key, false);
        
        return json_decode($decryptedData, true);
    }

    private function logApiError(\Exception $e, $apiName, $payload = null)
    {
        ApiErrorLog::create([
            'api_name'      => $apiName,
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
            'request_data'  => is_string($payload) ? $payload : json_encode($payload),
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ]);
    }

    private function getIfmsAuthCache()
    {
        $ifmsCache = Cache::get('IFMS_AUTH');
        if ($ifmsCache) {
            try {
                return Crypt::decrypt($ifmsCache);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    private function generateAES256Key()
    {
        return random_bytes(32);
    }

    private function encryptSymmetricKey($symmetricKey, $publicKeyPath)
    {
        $publicKey = file_get_contents($publicKeyPath);
        openssl_public_encrypt($symmetricKey, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
        return $encrypted;
    }

    private function decryptAES256ECB($encryptedSekBase64, $symmetricKey, $returnBase64 = true)
    {
        $encryptedSek = base64_decode($encryptedSekBase64, true);
        if ($encryptedSek === false) {
            throw new \Exception("Invalid base64 SEK");
        }

        $decrypted = openssl_decrypt(
            $encryptedSek,
            'AES-256-ECB',
            $symmetricKey,
            OPENSSL_RAW_DATA
        );

        if ($decrypted === false) {
            throw new \Exception("openssl_decrypt() failed — wrong AES key");
        }

        return $returnBase64 ? base64_encode($decrypted) : $decrypted;
    }

    private function encryptAES256Base64($data, $base64Sek)
    {
        $sek = str_pad(base64_decode($base64Sek, true), 32, "\0");
        if (!$sek || strlen($sek) !== 32) {
            throw new \Exception("Invalid SEK for encryption");
        }

        $encrypted = openssl_encrypt($data, 'AES-256-ECB', $sek, OPENSSL_RAW_DATA);
        return base64_encode($encrypted);
    }

    private function generateHmacBase64($data, $Sek)
    {
        $sek = base64_decode($Sek, true);
        if (!$sek || strlen($sek) !== 32) {
            throw new \Exception("Invalid SEK for HMAC");
        }

        return base64_encode(hash_hmac('sha256', $data, $sek, true));
    }
}
