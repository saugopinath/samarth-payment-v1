<?php

namespace App\Services\Integration\IFMS;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Services\Contracts\PaymentSBIIntegrationInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Scheme;
use Carbon\Carbon;
use App\Helpers\IFMSEncryptDecrypt;
use GuzzleHttp\Client;

class IfmsApiIntegrationService implements PaymentSBIIntegrationInterface
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new IfmsApiIntegrationService();
        }
        return self::$instance;
    }

    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS API payload generation
        return true;
    }

    public function pushToTarget(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS API push
        return [
            'status' => 1,
            'msg' => 'IFMS API push not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkAcknowledge(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS API acknowledge
        return [
            'status' => 1,
            'msg' => 'IFMS API acknowledge not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function checkResponse(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS API response
        return [
            'status' => 1,
            'msg' => 'IFMS API response not yet implemented for Lot - ' . $lotMaster->lot_no,
            'type' => 'blue'
        ];
    }

    public function authiticated($client_id, $client_secret)
    {
        try {
            // dd('ok');
            $clientId = $client_id;
            // $clientId = 'gen047';
            // $clientSecret = '9005694D7E0818B4A788465A050A7778F9CF06008B9C44205A59C1ACDEEBB1FE0F74E0CCEA5F9BC46B1A7C3955F02E7EA9D7BAA8206453D07BC30D8429710D1F';
            $clientSecret = $client_secret;
            // $clientSecret = 'E94AFEC0357C49363567D7C3E6B86F6A0C86F8B02EE760ACB7F83E00D612F67C8F7ED417942723B97C5A7C1D84EC2CB2AC55F811F94FA9C1DEB7BEF09501C8A2';
            // $postUrl = 'https://uat.wbifms.gov.in/food/main/version/authenticate';
            $postUrl = 'https://www.wbifms.gov.in/food/main/version/authenticate';
            $publicKeyPath = storage_path('app/IFMS/publicKey.pem');
            $JBprivateKeyPath = storage_path('app/IFMS/Jb_key/PrivateKey.pem');
            // dd($JBprivateKeyPath);
            $symmetricKey = IFMSEncryptDecrypt::generateAES256Key();
            $encryptedAppKey = IFMSEncryptDecrypt::encryptSymmetricKey($symmetricKey, $publicKeyPath);
            //  var_dump($encryptedAppKey);die;
            // dd(base64_encode($encryptedAppKey));
            $headers = [
                'Content-Type: application/json',
                'clientId: ' . $clientId,
                'clientSecret: ' . $clientSecret,
            ];
            $payload = [
                'appKey' => base64_encode($encryptedAppKey),
            ];
            $curl = curl_init($postUrl);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


            // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

            $response = curl_exec($curl);
            // dd($response);
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_response_log')->insert([
                'request_payload'               => json_encode($payload),
                'encrypted_res_payload'         => $response,
                'created_at'                    => date("Y-m-d H:i:s"),
                'client_id'                     => $clientId,
                'post_url'                      => $postUrl
            ]);
            // dd($log_insert);
            if (curl_errno($curl)) {
                // dd($curl);
                // dd(curl_error($curl));
                throw new \Exception('CURL Error: ' . curl_error($curl));
            }
            curl_close($curl);
            $responseData = json_decode($response, true);
            if (!isset($responseData['status']) || !$responseData['status']) {
                throw new \Exception('Authentication failed: ' . ($responseData['errorMessage'] ?? 'Unknown error'));
            }
            $authToken = $responseData['data']['authToken'] ?? null;
            $sek = $responseData['data']['sek'] ?? null;
            // dd($sek);
            if (!$authToken || !$sek) {
                throw new \Exception('Missing authToken or SEK in response');
            }
            $decryptedSek = IFMSEncryptDecrypt::decrypt($sek, $symmetricKey);
            $file_log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_status_log')->insert([
                'bill_type_id'                  => 'Auth',
                'auth_token'                    => $authToken,
                'sek'                           => $sek,
                'post_url'                      => $postUrl,
                'client_id'                     => $clientId,
                'request_payload'               => json_encode($payload),
                'response_payload'              => json_encode($responseData),
                'created_at'                    => date("Y-m-d H:i:s")
            ]);
            // Cache::put('IFMS_authToken', $authToken, Carbon::now()->addMinutes(30));
            // Cache::put('IFMS_sek', $decryptedSek, Carbon::now()->addMinutes(30));
            // Cache::put('IFMS_appKey_raw', $symmetricKey, Carbon::now()->addMinutes(30));
            // Cache::put('IFMS_sek_raw', $sek, Carbon::now()->addMinutes(30));

            Cache::put('IFMS_AUTH', [
                'client_id'  => $client_id,
                'IFMS_authToken'  => $authToken,
                'IFMS_sek'        => $decryptedSek,
                'IFMS_sek_raw'    => $sek,
                'IFMS_appKey_raw' => $symmetricKey,
            ], Carbon::now()->addMinutes(30));

            $dynamicKey = 'IFMS_authToken' . $client_id;
            $dynamicValue = [$authToken];
            $cart[$dynamicKey] = $dynamicValue;
            return true;
        } catch (\Exception $e) {
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.error_log')->insert([
                'exception'  => $e->getMessage(),
                'created_at' => date("Y-m-d H:i:s")
            ]);
            //throw $th;
            // dd($e);
        }
    }

    public function billSharing($payload, $lot_no, $DRNNo, $client_id, $client_secret)
    {
        try {
            $return_arr = array();
            $status = NULL;
            $rek = NULL;
            $data = NULL;
            $hmac = NULL;
            $statusMsg = NULL;
            $responseArray = NULL;
            // $authenticated = $this->authiticated($client_id);
            // $post_url = 'https://uat.wbifms.gov.in/food/main/version/bill-details';
            $post_url = 'https://www.wbifms.gov.in/food/main/version/bill-details';
            // Retrieve auth token and SEK
            // $authToken = Cache::get('IFMS_AUTH');
            // $sek = Cache::get('IFMS_sek'); // base64-encoded SEK
            // $sek_raw = Cache::get('IFMS_sek_raw');
            // $appKey =  Cache::get('IFMS_appKey_raw');
            // if (!$authToken || !$sek) {
            //     $authenticated = $this->authiticated($client_id, $client_secret);
            //     if (!$authenticated) {
            //         throw new \Exception("Authentication failed.");
            //     }
            //     $authToken = Cache::get('IFMS_AUTH');
            //     $sek = Cache::get('IFMS_sek');
            //     $sek_raw = Cache::get('IFMS_sek_raw');
            //     $appKey =  Cache::get('IFMS_appKey_raw');
            // }
            $ifmsCache = Cache::get('IFMS_AUTH');
            // Cache::forget('IFMS_AUTH');
            // Check if cache exists AND client_id matches
            if (
                empty($ifmsCache) ||
                empty($ifmsCache['IFMS_authToken']) ||
                empty($ifmsCache['IFMS_sek']) ||
                $ifmsCache['client_id'] !== $client_id
            ) {
                $authenticated = $this->authiticated($client_id, $client_secret);
                if (!$authenticated) {
                    Cache::forget('IFMS_AUTH');
                    $authenticated = $this->authiticated($client_id, $client_secret);
                }
                $ifmsCache = Cache::get('IFMS_AUTH');
            }

            $authToken = $ifmsCache['IFMS_authToken'];
            $sek = $ifmsCache['IFMS_sek'];
            $sek_raw = $ifmsCache['IFMS_sek_raw'];
            $appKey = $ifmsCache['IFMS_appKey_raw'];

            // ✅ 1. Encode payload with unescaped slashes + unicode
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $jsonPayload = trim($jsonPayload); // remove whitespace
            $jsonPayload = preg_replace('/\x{FEFF}/u', '', $jsonPayload); // remove BOM
            // dd($jsonPayload);
            // $jsonPayload = '{"genEpayment":{"drn":"202512045000391","ebopFlag":"B","benfFlag":"4","treasuryCode":"CAC","ddoCode":"CACICA102","schemeCode":"0","claimId":null,"headofAccount":"30-2235-60-102-022-31-02-V","grossAmount":10000,"netAmount":10000,"billType":"TR-31","sanctionNumber":"Test_LPP_12102025","sanctionDate":"15/12/2025","issueingAuth":"INC_PE","sanctionAmount":10000,"subDetail":[{"subdetailHead":null,"subdetailAmount":null}],"byTranfer":[{"btHoa":null,"btEffect":null,"btAmount":null}]}}';
            $encryptedData = IFMSEncryptDecrypt::encryptAES256Base64($jsonPayload, $sek);
            $hmac = IFMSEncryptDecrypt::generateHmacBase64($jsonPayload, $sek);
            $requestPayload = [
                'hmac' => $hmac,
                'data' => $encryptedData
            ];

            $headers = [
                'Content-Type: application/json',
                'clientId: ' . $client_id,
                'authToken: ' . $authToken,
            ];

            // ✅ 5. Send POST request
            $curl = curl_init($post_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestPayload));
            $post_response = curl_exec($curl);
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_response_log')->insert([
                'request_payload'               => json_encode($payload),
                'encrypted_res_payload'         => $post_response,
                'created_at'                    => date("Y-m-d H:i:s"),
                'client_id'                     => $client_id,
                'post_url'                      => $post_url,
                'lot_no'                        => $lot_no
            ]);
            // dd($post_response);
            if (curl_errno($curl)) {
                echo 'Curl error: ' . curl_error($curl);
                curl_close($curl);
                exit;
            }
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            // ✅ 6. Decode and return response
            if ($httpcode !== 200) {
                new \Exception('IFMS HTTP ERROR: ' . $httpcode);
            }

            $responseData = json_decode($post_response, true);
            if (isset($responseData['status']) && $responseData['status'] == true && $responseData['data'] && $responseData['rek'] && $responseData['hmac']) {
                $status = 1;
                $rek = $responseData['rek'];
                $data = $responseData['data'];
                $hmac = $responseData['hmac'];
                // $sek_decript = Cache::get('IFMS_sek');
                // $sek = str_pad(base64_decode($sek_decript, true), 32, "\0");
                // // dd($sek);
                // $decryptedrek = IFMSEncryptDecrypt::decrypt1($rek, $sek);
                // // dd($decryptedrek);
                // $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");
                // $decryptedrek1 = IFMSEncryptDecrypt::decrypt2($responseData['data'], $rek_key);
                // // dd( $decryptedrek1);
                // $responseArray = json_decode($decryptedrek1, true);
                // $statusMsg = $responseArray['statusMsg'];
                // $decryptedSek = IFMSEncryptDecrypt::decryptSEK($rek, $appKey);
                $sek = str_pad(base64_decode($sek, true), 32, "\0");
                $decryptedrek = IFMSEncryptDecrypt::decrypt1($rek, $sek);
                $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");
                $decryptedData = IFMSEncryptDecrypt::decrypt2($data, $rek_key);
                $responseArray = json_decode($decryptedData, true);
                $statusMsg = $responseArray['statusMsg'];
                // dd($responseArray);

                $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_data_shared_log')->insert([
                    'lot_no'                        => $lot_no,
                    'drn_no'                        => $DRNNo,
                    'bill_share_request_payload'    => $jsonPayload,
                    'bill_share_response_payload'   =>  json_encode($responseData, JSON_UNESCAPED_SLASHES),
                    'sek'                           => $sek_raw,
                    'bill_share_rek'                => $rek,
                    'created_at'                    => date("Y-m-d H:i:s")
                ]);
            }
            $file_log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_status_log')->insert([
                'bill_type_id'                  => 'Bill Share',
                'auth_token'                    => $authToken,
                'sek'                           => $sek_raw,
                'post_url'                      => $post_url,
                'client_id'                     => $client_id,
                'drn_no'                        => $DRNNo,
                'lot_no'                        => $lot_no,
                'request_payload'               => $jsonPayload,
                'response_payload'              => json_encode($responseArray, JSON_UNESCAPED_SLASHES),
                // 'encrypted_res_payload'         => $responseData,
                'created_at'                    => date("Y-m-d H:i:s")
            ]);
            $return_arr['status'] = $status;
            $return_arr['data'] = $data;
            $return_arr['rek'] = $rek;
            $return_arr['hmac'] = $hmac;
            $return_arr['statusMsg'] = $statusMsg;
            // dd($return_arr);
            return $return_arr;
        } catch (\Exception $e) {
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.error_log')->insert([
                'exception'  => $e->getMessage(),
                'created_at' => date("Y-m-d H:i:s")
            ]);
            // $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_response_log')->insert([
            //     'request_payload_1'               => $e
            //     // 'encrypted_res_payload'         => $post_response,
            //     // 'created_at'                    => date("Y-m-d H:i:s"),
            //     // 'client_id'                     => $client_id,
            //     // 'post_url'                      => $post_url,
            //     // 'lot_no'                        => $lot_no
            // ]);
            // dd($e);
        }
    }

    public function BeneficiaryShared($jsonData, $client_id, $drn_no, $lot_no, $client_secret)
    {
        try {
            $return_arr = [];
            $status = null;
            $rek = null;
            $data = null;
            $hmac = null;
            $statusMsg = null;
            $beneficiary = null;
            $responseArray = null;
            $res_drn = null;
            // $post_url = 'https://uat.wbifms.gov.in/food/main/version/beneficiary-details'; // FIXED URL (removed extra ///)
            $post_url = 'https://www.wbifms.gov.in/food/main/version/beneficiary-details';
            // $authToken = Cache::get('IFMS_authToken');
            // $sek = Cache::get('IFMS_sek'); // Must be raw decrypted bytes
            // $sek_raw = Cache::get('IFMS_sek_raw');
            // if (!$authToken || !$sek) {
            //     $this->authiticated($client_id, $client_secret);
            //     $authToken = Cache::get('IFMS_authToken');
            //     $sek = Cache::get('IFMS_sek');
            //     $sek_raw = Cache::get('IFMS_sek_raw');
            // }
            $ifmsCache = Cache::get('IFMS_AUTH');
            if (
                empty($ifmsCache) ||
                empty($ifmsCache['IFMS_authToken']) ||
                empty($ifmsCache['IFMS_sek']) ||
                $ifmsCache['client_id'] !== $client_id
            ) {
                $authenticated = $this->authiticated($client_id, $client_secret);
                if (!$authenticated) {
                    Cache::forget('IFMS_AUTH');
                    $authenticated = $this->authiticated($client_id, $client_secret);
                }
                $ifmsCache = Cache::get('IFMS_AUTH');
            }
            $authToken = $ifmsCache['IFMS_authToken'];
            $sek = $ifmsCache['IFMS_sek'];
            $sek_raw = $ifmsCache['IFMS_sek_raw'];
            $curl = curl_init($post_url);
            $headers = [
                'Content-Type: application/json',
                'clientId: ' . $client_id,
                'authToken: ' . $authToken,
            ];
            $jsonPayload = json_encode($jsonData);
            $encryptedData = IFMSEncryptDecrypt::encryptAES256Base64($jsonPayload, $sek);
            $hmac = IFMSEncryptDecrypt::generateHmacBase64($jsonPayload, $sek);
            $payload = json_encode([
                'hmac' => $hmac,
                'data' => $encryptedData
            ]);
            curl_setopt_array($curl, [
                CURLOPT_URL => $post_url,
                CURLOPT_HTTPHEADER => $headers,
                // CURLOPT_SSL_VERIFYHOST => 0,
                // CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,  // FIX 2: JSON string must be sent, not an array
            ]);

            $post_response = curl_exec($curl);
            // dd($post_response);
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_response_log')->insert([
                'request_payload'               => json_encode($payload),
                'encrypted_res_payload'         => $post_response,
                'created_at'                    => date("Y-m-d H:i:s"),
                'client_id'                     => $client_id,
                'post_url'                      => $post_url,
                'lot_no'                        => $lot_no
            ]);
            // dd( $post_response);
            if (curl_errno($curl)) {
                $response_text = curl_error($curl);
                curl_close($curl);
                dd("CURL ERROR: " . $response_text);
            }
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($httpcode !== 200) {
                new \Exception('IFMS HTTP ERROR: ' . $httpcode);
            }
            $responseData = json_decode($post_response, true);
            // dd($responseData);
            // var_dump($responseData);die;
            $status = isset($responseData['status']) ? $responseData['status'] : null;
            $rek = isset($responseData['rek']) ? $responseData['rek'] : null;
            $data = isset($responseData['data']) ? $responseData['data'] : null;
            $drn = isset($responseData['drn']) ? $responseData['drn'] : null;
            if ($status == true && $rek && $data) {
                $status = 1;
                $sek = str_pad(base64_decode($sek, true), 32, "\0");
                $decryptedrek = IFMSEncryptDecrypt::decrypt1($rek, $sek);
                $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");
                $decryptedData = IFMSEncryptDecrypt::decrypt2($data, $rek_key);
                $responseArray = json_decode($decryptedData, true);
                // dd( $responseArray);
                $res_drn = isset($responseArray['drn']) ? $responseArray['drn'] : null;
                $statusMsg = isset($responseArray['statusMsg']) ? $responseArray['statusMsg'] : null;
                $beneficiary = isset($responseArray['beneficiary']) ? $responseArray['beneficiary'] : null;
                $request_payload = $payload;
                $response_payload = $responseData;
                $now = Carbon::now();
                // $log_update = DB::connection('pgsql_paywrite')->table('ifms.ifms_data_shared_log')->where('drn_no', $drn_no)->update([
                //     'ben_share_request_payload' => $request_payload,
                //     'ben_share_response_payload' => json_encode($response_payload, JSON_UNESCAPED_SLASHES),
                //     'updated_at' => $now
                // ]);
            } else if ($status == null && $drn) {
                $responseArray = $responseData;
                $status = 2;
                $statusMsg = isset($responseData['statusMsg']) ? $responseData['statusMsg'] : null;
                $beneficiary = isset($responseData['beneficiary']) ? $responseData['beneficiary'] : null;
            }
            $file_log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_status_log')->insert([
                'bill_type_id'                  => 'BenShare',
                'auth_token'                    => $authToken,
                'sek'                           => $sek_raw,
                'post_url'                      => $post_url,
                'client_id'                     => $client_id,
                'drn_no'                        => $drn_no,
                // 'lot_no'                        => $lot_no,
                'request_payload'               => $jsonPayload,
                'response_payload'              => json_encode($responseArray, JSON_UNESCAPED_SLASHES),
                // 'encrypted_res_payload'         => $responseData,
                'created_at'                    => date("Y-m-d H:i:s")
            ]);
            $return_arr['status'] = $status;
            $return_arr['drn'] = $res_drn;
            $return_arr['statusMsg'] = $statusMsg;
            $return_arr['beneficiary'] = $beneficiary;
            //  dd($return_arr);
            return $return_arr;
        } catch (\Exception $e) {
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.error_log')->insert([
                'exception'  => $e->getMessage(),
                'created_at' => date("Y-m-d H:i:s")
            ]);
            // dd($e);
        }
    }
    public function billGenerated($drn_no, $client_id, $lot_no, $client_secret)
    {
        try {
            $return_arr = array();
            $status = NULL;
            $rek = NULL;
            $data = NULL;
            $hmac = NULL;
            $statusMsg = NULL;
            $referenceNumber = NULL;
            $billStatus = NULL;
            $reason = NULL;
            $responseArray = NULL;
            // $post_url = 'https://uat.wbifms.gov.in/food/main/version/get-bill-status';
            $post_url = 'https://www.wbifms.gov.in/food/main/version/get-bill-status';
            // $authToken = Cache::get('IFMS_authToken');
            // $sek = Cache::get('IFMS_sek'); // MUST be raw bytes from decrypted SEK
            // $sek_raw = Cache::get('IFMS_sek_raw');
            // // dd($authToken);
            // if (!$authToken || !$sek) {
            //     $this->authiticated($client_id, $client_secret);

            //     $authToken = Cache::get('IFMS_authToken');
            //     $sek = Cache::get('IFMS_sek');
            //     $sek_raw = Cache::get('IFMS_sek_raw');
            // }
            $ifmsCache = Cache::get('IFMS_AUTH');
            if (
                empty($ifmsCache) ||
                empty($ifmsCache['IFMS_authToken']) ||
                empty($ifmsCache['IFMS_sek']) ||
                $ifmsCache['client_id'] !== $client_id
            ) {
                $authenticated = $this->authiticated($client_id, $client_secret);
                if (!$authenticated) {
                    Cache::forget('IFMS_AUTH');
                    $authenticated = $this->authiticated($client_id, $client_secret);
                }
                $ifmsCache = Cache::get('IFMS_AUTH');
            }
            $authToken = $ifmsCache['IFMS_authToken'];
            $sek = $ifmsCache['IFMS_sek'];
            $sek_raw = $ifmsCache['IFMS_sek_raw'];
            $payloadObj = [
                "drn" => $drn_no
            ];
            $jsonPayload = json_encode($payloadObj, JSON_UNESCAPED_SLASHES);
            // Encrypt JSON
            $encryptedData = IFMSEncryptDecrypt::encryptAES256Base64($jsonPayload, $sek);
            // Generate HMAC using encrypted bytes
            $hmac = IFMSEncryptDecrypt::generateHmacBase64($jsonPayload, $sek);
            $payload = json_encode([
                "hmac" => $hmac,
                "data" => $encryptedData
            ]);
            $headers = [
                'Content-Type: application/json',
                'clientId: ' . $client_id,
                'authToken: ' . $authToken,
            ];

            // 5️⃣ Send request
            $curl = curl_init($post_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

            $response = curl_exec($curl);
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_response_log')->insert([
                'request_payload'               => json_encode($payload),
                'encrypted_res_payload'         => $response,
                'created_at'                    => date("Y-m-d H:i:s"),
                'client_id'                     => $client_id,
                'post_url'                      => $post_url,
                'lot_no'                        => $lot_no
            ]);
            if (curl_errno($curl)) {
                dd("Curl Error: " . curl_error($curl));
            }
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($httpcode !== 200) {
                new \Exception('IFMS HTTP ERROR: ' . $httpcode);
            }
            // dd($response);
            $responseData = json_decode($response, true);
            // dd($responseData);
            $status = isset($responseData['status']) ? $responseData['status'] : null;
            $rek = isset($responseData['rek']) ? $responseData['rek'] : null;
            $data = isset($responseData['data']) ? $responseData['data'] : null;
            if ($status == true) {
                $sek = str_pad(base64_decode($sek, true), 32, "\0");
                $decryptedrek = IFMSEncryptDecrypt::decrypt1($rek, $sek);
                $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");
                $decryptedData = IFMSEncryptDecrypt::decrypt2($responseData['data'], $rek_key);
                //  dd( $decryptedData);
                // $decryptedData = '"{"drn":202512046000432,"referenceNumber":"202512046000432","billStatus":"Reference Generated"}"';
                $responseArray = json_decode($decryptedData, true);
                // dd($responseArray);
                $drn = isset($responseArray['drn']) ? $responseArray['drn'] : null;
                $referenceNumber = isset($responseArray['referenceNumber']) ? $responseArray['referenceNumber'] : null;
                $billStatus = isset($responseArray['billStatus']) ? $responseArray['billStatus'] : null;
                $reason = isset($responseArray['reason']) ? $responseArray['reason'] : null;
                $status = 1;
                $request_payload = $payload;
                $response_payload = $responseData;
                $res_data = $data;
                $now = Carbon::now();
                // $log_update = DB::connection('pgsql_paywrite')->table('ifms.ifms_data_shared_log')->where('drn_no', $drn_no)->update([
                //     'bill_generate_request_payload' => $request_payload,
                //     'bill_generate_response_payload' => json_encode($response_payload, JSON_UNESCAPED_SLASHES),
                //     'bill_status' => $billStatus,
                //     'referenceNumber' => $referenceNumber,
                //     'updated_at' => $now
                // ]);
            }
            $file_log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_status_log')->insert([
                'bill_type_id'                  => 'BillACK',
                'auth_token'                    => $authToken,
                'sek'                           => $sek_raw,
                'post_url'                      => $post_url,
                'client_id'                     => $client_id,
                'drn_no'                        => $drn_no,
                'lot_no'                        => $lot_no,
                'request_payload'               => $jsonPayload,
                'response_payload'              => json_encode($responseArray, JSON_UNESCAPED_SLASHES),
                // 'encrypted_res_payload'         => $responseData,
                'created_at'                    => date("Y-m-d H:i:s")
            ]);
            $return_arr['status'] = $status;
            // $return_arr['drn'] = $drn;
            $return_arr['referenceNumber'] = $referenceNumber;
            //  $return_arr['referenceNumber'] = '123456789';
            // $return_arr['billStatus'] = 'Reference Generated';
            $return_arr['billStatus'] = $billStatus;
            $return_arr['reason'] = $reason;
            //  dd($return_arr);
            return $return_arr;
        } catch (\Exception $e) {
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.error_log')->insert([
                'exception'  => $e->getMessage(),
                'created_at' => date("Y-m-d H:i:s")
            ]);
            // dd($e);
        }
    }
    public function BeneficiaryResponse($client_id, $drn_no, $client_secret, $lot_no)
    {
        try {
            $return_arr = [];
            $status = null;
            $payment_details = null;
            $rek = null;
            $data = null;
            $hmac = null;
            $res_drn = null;
            $statusMsg = null;
            $responseArray = null;
            $beneficiary_details = null;
            // $post_url = 'https://uat.wbifms.gov.in/food/main/version/payment-success-failure-info';
            $post_url = 'https://www.wbifms.gov.in/food/main/version/payment-success-failure-info';
            // $authToken = Cache::get('IFMS_authToken');
            // $sek = Cache::get('IFMS_sek'); // Must be raw decrypted bytes
            // $sek_raw = Cache::get('IFMS_sek_raw');
            // if (!$authToken || !$sek) {
            //     $this->authiticated($client_id, $client_secret);
            //     $authToken = Cache::get('IFMS_authToken');
            //     $sek = Cache::get('IFMS_sek');
            //     $sek_raw = Cache::get('IFMS_sek_raw');
            // }
            $ifmsCache = Cache::get('IFMS_AUTH');
            if (
                empty($ifmsCache) ||
                empty($ifmsCache['IFMS_authToken']) ||
                empty($ifmsCache['IFMS_sek']) ||
                $ifmsCache['client_id'] !== $client_id
            ) {
                $authenticated = $this->authiticated($client_id, $client_secret);
                if (!$authenticated) {
                    Cache::forget('IFMS_AUTH');
                    $authenticated = $this->authiticated($client_id, $client_secret);
                }
                $ifmsCache = Cache::get('IFMS_AUTH');
            }
            $authToken = $ifmsCache['IFMS_authToken'];
            $sek = $ifmsCache['IFMS_sek'];
            $sek_raw = $ifmsCache['IFMS_sek_raw'];
            $curl = curl_init($post_url);
            $headers = [
                'Content-Type: application/json',
                'clientId: ' . $client_id,
                'authToken: ' . $authToken,
            ];
            // dump($drn_no);
            // $jsonPayload = json_encode($drn_no);
            //  dd( $jsonPayload);
            $jsonPayload = [
                "drn" => $drn_no
            ];
            $jsonPayload = json_encode($jsonPayload, JSON_UNESCAPED_SLASHES);
            $encryptedData = IFMSEncryptDecrypt::encryptAES256Base64($jsonPayload, $sek);
            $hmac = IFMSEncryptDecrypt::generateHmacBase64($jsonPayload, $sek);
            $payload = json_encode([
                'hmac' => $hmac,
                'data' => $encryptedData
            ]);
            curl_setopt_array($curl, [
                CURLOPT_URL => $post_url,
                CURLOPT_HTTPHEADER => $headers,
                // CURLOPT_SSL_VERIFYHOST => 0,
                // CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,  // FIX 2: JSON string must be sent, not an array
            ]);

            $post_response = curl_exec($curl);
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_response_log')->insert([
                'request_payload'               => json_encode($payload),
                'encrypted_res_payload'         => $post_response,
                'created_at'                    => date("Y-m-d H:i:s"),
                'client_id'                     => $client_id,
                'post_url'                      => $post_url,
                'lot_no'                        => $lot_no
            ]);
            //  dd( $post_response);
            if (curl_errno($curl)) {
                $response_text = curl_error($curl);
                curl_close($curl);
                dd("CURL ERROR: " . $response_text);
            }
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($httpcode !== 200) {
                new \Exception('IFMS HTTP ERROR: ' . $httpcode);
            }
            $responseData = json_decode($post_response, true);
            // dd($responseData);
            // var_dump($responseData);die;
            $status = isset($responseData['status']) ? $responseData['status'] : null;
            $data = isset($responseData['data']) ? $responseData['data'] : null;
            $rek = isset($responseData['rek']) ? $responseData['rek'] : null;
            $hmac = isset($responseData['hmac']) ? $responseData['hmac'] : null;
            if ($status == true) {
                $status = 1;
                $sek = str_pad(base64_decode($sek, true), 32, "\0");
                $decryptedrek = IFMSEncryptDecrypt::decrypt1($rek, $sek);
                $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");
                $decryptedData = IFMSEncryptDecrypt::decrypt2($data, $rek_key);
                // dd($decryptedData);
                //                 $decryptedData='{
                // "drn":"202512046000432","totalAmount":"2000","benfCount":"2",
                // "beneficiary":[{"benfName": "SADI BARMAN","accountNumber": "5488026123638","ifsCode": "PUNB0RRBBGB","mobileNo": "8617267761","amount": "1000","benfUniqueId": "31385635084",
                // "orderNo": "4676777","uniqueId": "31385635084","panNumber": "HGTRE5431H","tdsAmount": "10" },
                // {"benfName": "Indrani Chatterjee","accountNumber": "34147683408","ifsCode": "SBIN0012423","mobileNo": "9674924790","amount": "1000","benfUniqueId": "31385871789",
                // "orderNo": "4676777","uniqueId": "31385871789","panNumber": "HGTRE5431H","tdsAmount": "10" }
                // ]
                // }
                //     ';
                //                $decryptedData = '{
                //     "paymentDetails": {
                //         "drn": "202512046000432",
                //         "voucherNo": "27654",
                //         "voucherDate": "10/01/2023",
                //         "tokenNumber": "123",
                //         "tokenDate": "10/01/2023",
                //         "beneficiaryDetail": [
                //             {
                //                 "accountNumber": "5488026123638",
                //                 "amount": "1000",
                //                 "ifsCode": "PUNB0RRBBGB",
                //                 "paymentDate": "10/01/2023",
                //                 "reason": "",
                //                 "benfUniqueId": "31385635084",
                //                 "orderNo": "4676777",
                //                 "referenceNumber": "202512046000432",
                //                 "status": "Success",
                //                 "utrNo": "U076566777"
                //             },
                //             {
                //                 "accountNumber": "34147683408",
                //                 "amount": "1000",
                //                 "ifsCode": "SBIN0012423",
                //                 "paymentDate": "10/01/2023",
                //                 "reason": "",
                //                 "benfUniqueId": "31385871789",
                //                 "orderNo": "4676777",
                //                 "referenceNumber": "202512046000432",
                //                 "status": "Success",
                //                 "utrNo": "U076566777"
                //             }
                //         ]
                //     }
                // }';

                $responseArray = json_decode($decryptedData, true);
                // dd($responseArray);
                // $res_drn = isset($responseArray['drn']) ? $responseArray['drn'] : null;
                // $statusMsg = isset($responseArray['statusMsg']) ? $responseArray['statusMsg'] : null;
                $payment_details = isset($responseArray['paymentDetails']) ? $responseArray['paymentDetails'] : null;
                $beneficiary_details = isset($responseArray['beneficiaryDetails']) ? $responseArray['beneficiaryDetails'] : null;
                $request_payload = $payload;
                $response_payload = $responseData;
                $now = Carbon::now();
                // $log_update = DB::connection('pgsql_paywrite')->table('ifms.ifms_data_shared_log')->where('drn_no', $drn_no)->update([
                //     'ben_response_request_payload' => $request_payload,
                //     'ben_response_receive_payload' => json_encode($response_payload, JSON_UNESCAPED_SLASHES),
                //     'updated_at' => $now
                // ]);
            }
            $file_log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_status_log')->insert([
                'bill_type_id'                  => 'BillResponse',
                'auth_token'                    => $authToken,
                'sek'                           => $sek_raw,
                'post_url'                      => $post_url,
                'client_id'                     => $client_id,
                'drn_no'                        => $drn_no,
                // 'lot_no'                        => $lot_no,
                'request_payload'               => $jsonPayload,
                'response_payload'              => json_encode($responseArray, JSON_UNESCAPED_SLASHES),
                // 'encrypted_res_payload'         => $responseData,
                'created_at'                    => date("Y-m-d H:i:s")
            ]);
            $return_arr['status'] = $status;
            $return_arr['payment_details'] = $payment_details;
            $return_arr['beneficiary_details'] = $beneficiary_details;

            // dd($return_arr);
            return $return_arr;
        } catch (\Exception $e) {
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.error_log')->insert([
                'exception'  => $e->getMessage(),
                'created_at' => date("Y-m-d H:i:s")
            ]);
            // dd($e);
        }
    }
    public function Allotement_data($client_id, $hoa_arr, $client_secret)
    {
        try {
            $return_arr = [];
            $status = null;
            $rek = null;
            $data = null;
            $hmac = null;
            $headofAccount = null;
            $progressiveExp = null;
            $ddoBalance = null;
            $treasuryBalance = null;
            $responseArray = null;
            // $post_url = 'https://uat.wbifms.gov.in/food/main/version/hoa-balance';
            $post_url = 'https://www.wbifms.gov.in/food/main/version/hoa-balance';
            // $authToken = Cache::get('IFMS_authToken');
            // $sek = Cache::get('IFMS_sek'); // Must be raw decrypted bytes
            // $sek_raw = Cache::get('IFMS_sek_raw');
            // if (!$authToken || !$sek) {
            // $this->authiticated($client_id, $client_secret);
            // $authToken = Cache::get('IFMS_authToken');
            // $sek = Cache::get('IFMS_sek');
            // $sek_raw = Cache::get('IFMS_sek_raw');
            // }
            // $sek = base64_decode($sek);
            $ifmsCache = Cache::get('IFMS_AUTH');
            if (
                empty($ifmsCache) ||
                empty($ifmsCache['IFMS_authToken']) ||
                empty($ifmsCache['IFMS_sek']) ||
                $ifmsCache['client_id'] !== $client_id
            ) {
                $authenticated = $this->authiticated($client_id, $client_secret);
                if (!$authenticated) {
                    Cache::forget('IFMS_AUTH');
                    $authenticated = $this->authiticated($client_id, $client_secret);
                }
                $ifmsCache = Cache::get('IFMS_AUTH');
            }
            $sek = $ifmsCache['IFMS_sek'];
            $sek_raw = $ifmsCache['IFMS_sek_raw'];

            $authToken = $ifmsCache['IFMS_authToken'];
            $curl = curl_init($post_url);
            $headers = [
                'Content-Type: application/json',
                'clientId: ' . $client_id,
                'authToken: ' . $authToken,
            ];
            // $jsonPayload = json_encode(value: $hoa_arr);
            $jsonPayload = json_encode($hoa_arr, JSON_UNESCAPED_SLASHES);
            // dump($client_id);
            // dd($jsonPayload);
            $encryptedData = IFMSEncryptDecrypt::encryptAES256Base64($jsonPayload, $sek);
            // dd($encryptedData);
            $hmac = IFMSEncryptDecrypt::generateHmacBase64($jsonPayload, $sek);
            // dump('raw_data=>' . $jsonPayload);
            // dump('encrypted_data=>' . $encryptedData);
            // var_dump('encrypted_hmac=>' . $hmac);
            // var_dump($jsonPayload);
            // die;


            $payload = json_encode([
                'hmac' => $hmac,
                'data' => $encryptedData
            ]);
            curl_setopt_array($curl, [
                CURLOPT_URL => $post_url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_SSL_VERIFYHOST => 0,
                // CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,  // FIX 2: JSON string must be sent, not an array
            ]);

            $post_response = curl_exec($curl);
            // dd($post_response);
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_response_log')->insert([
                'request_payload'               => json_encode($payload),
                'encrypted_res_payload'         => $post_response,
                'created_at'                    => date("Y-m-d H:i:s"),
                'client_id'                     => $client_id,
                'post_url'                      => $post_url
            ]);
            // dd($log_insert);
            if (curl_errno($curl)) {
                $response_text = curl_error($curl);
                curl_close($curl);
                dd("CURL ERROR: " . $response_text);
            }
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($httpcode !== 200) {
                new \Exception('IFMS HTTP ERROR: ' . $httpcode);
            }
            //  dump('encrypted_response=>'.$post_response);
            // dd($post_response);
            //  var_dump($httpcode);die;

            $responseData = json_decode($post_response, true);

            // dd($responseData);
            // var_dump($responseData);die;
            $status = isset($responseData['status']) ? $responseData['status'] : null;
            $data = isset($responseData['data']) ? $responseData['data'] : null;
            $rek = isset($responseData['rek']) ? $responseData['rek'] : null;
            $hmac = isset($responseData['hmac']) ? $responseData['hmac'] : null;
            //  $sek = str_pad(base64_decode($sek, true), 32, "\0");
            //         $decryptedrek = IFMSEncryptDecrypt::decrypt1($rek, $sek);
            //         $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");

            //    $decryptedData = IFMSEncryptDecrypt::decrypt2( $data, $rek_key);
            //                    $responseArray = json_decode($decryptedData, true);
            // dd( 'decrypted_response=>'.$responseArray);
            if ($status == true) {
                $status = 1;
                $sek = str_pad(base64_decode($sek, true), 32, "\0");
                $decryptedrek = IFMSEncryptDecrypt::decrypt1($rek, $sek);
                $rek_key = str_pad(base64_decode($decryptedrek, true), 32, "\0");
                $decryptedData = IFMSEncryptDecrypt::decrypt2($data, $rek_key);
                $responseArray = json_decode($decryptedData, true);
                // dd($responseArray);
                // $res_drn = isset($responseArray['drn']) ? $responseArray['drn'] : null;
                $headofAccount = isset($responseArray['headofAccount']) ? $responseArray['headofAccount'] : null;
                $progressiveExp = isset($responseArray['progressiveExp']) ? $responseArray['progressiveExp'] : null;
                $ddoBalance = isset($responseArray['ddoBalance']) ? $responseArray['ddoBalance'] : null;
                $treasuryBalance = isset($responseArray['treasuryBalance']) ? $responseArray['treasuryBalance'] : null;
            }
            $file_log_insert = DB::connection('pgsql_paywrite')->table('ifms.ifms_bill_status_log')->insert([
                'bill_type_id'                  => 'BillAllotment',
                'auth_token'                    => $authToken,
                'sek'                           => $sek_raw,
                'post_url'                      => $post_url,
                'client_id'                     => $client_id,
                'request_payload'               => $jsonPayload,
                'response_payload'              => json_encode($responseArray, JSON_UNESCAPED_SLASHES),
                // 'encrypted_res_payload'         => $responseData,
                'created_at'                    => date("Y-m-d H:i:s")
            ]);
            $return_arr['status'] = $status;
            $return_arr['headofAccount'] = $headofAccount;
            $return_arr['progressiveExp'] = $progressiveExp;
            $return_arr['ddoBalance'] = $ddoBalance;
            $return_arr['treasuryBalance'] = $treasuryBalance;
            //  dd($return_arr);
            return $return_arr;
        } catch (\Exception $e) {
            $log_insert = DB::connection('pgsql_paywrite')->table('ifms.error_log')->insert([
                'exception'  => $e->getMessage(),
                'created_at' => date("Y-m-d H:i:s")
            ]);
            // dd($e);
        }
    }
}
