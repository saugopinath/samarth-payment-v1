<?php

namespace App\Services\Payment\Commands\IfmsApi;

use App\Services\Payment\Contracts\PaymentStepCommand;
use App\Models\PaymentLotMaster;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\Payment\IfmsApiService;
use App\Models\ApiErrorLog;
use App\Models\ApiStepLog;

class BillGenerationCommand implements PaymentStepCommand
{
    protected $apiService;

    public function __construct(IfmsApiService $apiService)
    {
        $this->apiService = $apiService;
          $publicKeyPath = storage_path(config('services.ifms.public_key_path', 'app/IFMS/publicKey.pem'));
            if (file_exists($publicKeyPath)) {
                $keyData = file_get_contents($publicKeyPath);
                $certInfo = openssl_x509_parse($keyData);
                if ($certInfo && isset($certInfo['validTo_time_t'])) {
                    if (time() > $certInfo['validTo_time_t']) {
                        return response()->json(['error' => 'IFMS Public Key has expired.'], 400);
                    }
                }
            }
    }

    public function execute($lot, array $data = []): array
    {
        try {
            $lot_no = $lot->lot_no;
            $scheme_id = $lot->scheme_id;

            
            // Map the passed-in array values from our Modal to the variables expected by the logic
            $senctionNumber = trim($data['sanction_number'] ?? '');
            $senctionDate = trim($data['sanction_date'] ?? '');
            $issueingAuth = trim($data['issuing_authority'] ?? '');
            $bill_no = trim($data['bill_number'] ?? '');
            $bill_date = trim($data['bill_date'] ?? '');

            if ($senctionNumber == '' || $senctionDate == '' || $issueingAuth == '' || $scheme_id == '' || $bill_no == '' || $bill_date == '') {
                return ['status' => 0, 'msg' => 'Required fields are missing.', 'type' => 'red'];
            }

            $total_amount = $lot->total_amount;
            $grossAmount = $total_amount;
            $netAmount =  $total_amount;
            $claimId = NULL;
            $senctionAmount =  $total_amount;
            $ebopFlag = 'B';
            $schemeCode = '0';
            
            // Safe fallbacks in case schemeDetail is missing in development
            $treasuryCode = 'TEST';
            $ddoCode = 'TEST';
            $headofAccount = 'TEST';
            $billType = 'TEST';
            $client_id = 'TEST';
            $client_secret = 'TEST';
            
            $partyCode = 'TEST';
            $now = Carbon::now();
            $year = $now->format('Y');
            $month = $now->format('m');
            $nextSequence = '000001';
            $DRNNo = $year . $month . $partyCode . $nextSequence;
                
            $beneficiaryCount = 10;
            
            if ($beneficiaryCount <= 100) {
                $benfFlag = '1';
            } elseif ($beneficiaryCount <= 200) {
                $benfFlag = '2';
            } elseif ($beneficiaryCount <= 300) {
                $benfFlag = '3';
            } else {
                $benfFlag = '4';
            }
            
            $payload = [
                'genEpayment' => [
                    'drn' => $DRNNo,
                    'ebopFlag' => $ebopFlag,
                    'benfFlag' => $benfFlag,
                    'treasuryCode' => $treasuryCode,
                    'ddoCode' => $ddoCode,
                    'schemeCode' => $schemeCode,
                    'claimId' => $claimId,
                    'headofAccount' => $headofAccount,
                    'grossAmount' => $grossAmount,
                    'netAmount' => $netAmount,
                    'billType' => $billType,
                    'sanctionNumber' => $senctionNumber,
                    'sanctionDate' => $senctionDate,
                    'issueingAuth' => $issueingAuth,
                    'sanctionAmount' => $senctionAmount,
                    'billNo' => $bill_no,
                    'billDate' => $bill_date,
                    'subDetail' => [
                        [
                            'subdetailHead' => NULL,
                            'subdetailAmount' => NULL
                        ]
                    ],
                    'byTranfer' => [
                        [
                            'btHoa' => NULL,
                            'btEffect' => NULL,
                            'btAmount' => NULL
                        ]
                    ]
                ]
            ];
            
            ApiStepLog::create([
                    'user_id'    => auth()->id(),
                    'step_name'  => 'Bill Share',
                    'lot_no'     => $lot_no,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
            ]);

          

            $bill_share = $this->apiService->billSharing($payload, $lot_no, $DRNNo, $client_id, $client_secret);
            
            if ($bill_share) {
                $newStatus = Config::get('ifms.status.ifms_api.bill_gen');
                $next_level_code=Codemaster::where('short_name', $newStatus)->first();
                DB::connection('pgsql_payment')->beginTransaction();
                DB::connection('pgsql_ifms')->beginTransaction();
                $main_update=$lot->update(['cur_status' => $next_level_code->id]);
                $bill_share_to_ifms_status =IfmsPaymentLotMasterAdditionalInfo::where('lot_no', $lot_no)
                    ->where('scheme_id', $scheme_id)
                    ->update([
                        'sanctionAmount' => $senctionAmount, 
                        'issueingAuth' => $issueingAuth, 
                        'sanctionNumber' => $senctionNumber, 
                        'sanctionDate' => $senctionDate, 
                        'billNo' => $bill_no, 
                        'billDate' => $bill_date
                    ]);
                    
          
                
                if ($main_update && $bill_share_to_ifms_status) {
                    DB::connection('pgsql_payment')->commit();
                    DB::connection('pgsql_ifms')->commit();
                    
                    // Proceed to update the UI status state so the button advances
                    
                    
                    return [
                        'status' => 1,
                        'msg' => 'Lot No:- ' . $lot_no . ' has been shared to IFMS successfully.',
                        'type' => 'green'
                    ];
                } else {
                    DB::connection('pgsql_payment')->rollback();
                    DB::connection('pgsql_ifms')->rollback();
                    return [
                        'status' => 0,
                        'msg' => 'Status update error. Please Try again later.',
                        'type' => 'red'
                    ];
                }
            } else {
                return [
                    'status' => 0,
                    'msg' => 'File has not been shared. Please try after sometime.',
                    'type' => 'red'
                ];
            }
        } catch (\Exception $e) {
            ApiErrorLog::create([
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
            'request_data'  => isset($payload) ? (is_string($payload) ? $payload : json_encode($payload)) : null,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
             ]);
            
            return [
                'status' => 0,
                'msg' => 'Oops. Error occurred: ' . $e->getMessage(),
                'type' => 'red'
            ];
        }
    }
}
