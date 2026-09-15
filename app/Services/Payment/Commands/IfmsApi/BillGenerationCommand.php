<?php

namespace App\Services\Payment\Commands\IfmsApi;

use App\Services\Payment\Contracts\PaymentStepCommand;
use App\Models\PaymentLotMaster;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\Payment\IfmsApiService;

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

            // Using the requested DB queries
            $schemeDetail = DB::connection('pgsql_paywrite')->table('m_scheme')
                ->select('ddo_code', 'party_code', 'hoa_code', 'treasury_code', 'bill_type', 'client_id', 'client_secret')
                ->where('id', $scheme_id)
                ->first();
                
            $lot_details = DB::connection('pgsql_paywrite')->table('ifms.transaction_lot')
                ->select('amount_debit')
                ->where('lot_no', '=', $lot_no)
                ->where('scheme_id', '=', $scheme_id)
                ->first();
                
            $total_amount = $lot_details ? $lot_details->amount_debit : $lot->total_amount;
            $grossAmount = $total_amount;
            $netAmount =  $total_amount;
            $claimId = NULL;
            $senctionAmount =  $total_amount;
            $ebopFlag = 'B';
            $schemeCode = '0';
            
            // Safe fallbacks in case schemeDetail is missing in development
            $treasuryCode = $schemeDetail->treasury_code ?? 'TEST';
            $ddoCode = $schemeDetail->ddo_code ?? 'TEST';
            $headofAccount = $schemeDetail->hoa_code ?? 'TEST';
            $billType = $schemeDetail->bill_type ?? 'TEST';
            $client_id = $schemeDetail->client_id ?? 'TEST';
            $client_secret = $schemeDetail->client_secret ?? 'TEST';
            
            $nextSequenceResult = DB::connection('pgsql_paywrite')->select("SELECT LPAD(NEXTVAL('ifms.drn_ref_seq')::TEXT, 6, '0') AS seq");
            $nextSequence = $nextSequenceResult[0]->seq ?? '000001';
            
            $partyCode = $schemeDetail->party_code ?? 'TEST';
            $now = Carbon::now();
            $year = $now->format('Y');
            $month = $now->format('m');
            $DRNNo = $year . $month . $partyCode . $nextSequence;

            $existing_lot_details = DB::connection('pgsql_paywrite')->table('payment.lot_master')
                ->select('lot_month', 'lot_year')
                ->where('scheme_id', $scheme_id)
                ->first();
                
            $existing_filename_details = DB::connection('pgsql_paywrite')->table('payment.lot_master')
                ->select('ben_count')
                ->where('scheme_id', $scheme_id)
                ->where('lot_year', $existing_lot_details->lot_year ?? $lot->lot_year)
                ->where('lot_month', $existing_lot_details->lot_month ?? $lot->lot_month)
                ->first();
                
            $beneficiaryCount = $existing_filename_details ? $existing_filename_details->ben_count : $lot->ben_count;
            
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
                DB::connection('pgsql_payment')->beginTransaction();
                DB::connection('pgsql_ifms')->beginTransaction();
                
                $bill_share_to_ifms_status = DB::connection('pgsql_paywrite')->table('payment.lot_master')
                    ->where('lot_no', $lot_no)
                    ->where('scheme_id', $scheme_id)
                    ->update([
                        'bill_status' => 1, 
                        'drn_no' => $DRNNo, 
                        'sanctionAmount' => $senctionAmount, 
                        'issueingAuth' => $issueingAuth, 
                        'sanctionNumber' => $senctionNumber, 
                        'sanctionDate' => $senctionDate, 
                        'billNo' => $bill_no, 
                        'billDate' => $bill_date
                    ]);
                    
                DB::connection('pgsql_paywrite')->table('ifms.transaction_lot')
                    ->where('lot_no', $lot_no)
                    ->where('scheme_id', $scheme_id)
                    ->update([
                        'bill_status' => 1,
                        'drn_no' => $DRNNo
                    ]);
                
                if ($bill_share_to_ifms_status) {
                    DB::connection('pgsql_paywrite')->commit();
                    
                    // Proceed to update the UI status state so the button advances
                    $lot->update(['cur_status' => 'PLSIFMS_BILL_GEN']);
                    
                    return [
                        'status' => 1,
                        'msg' => 'Lot No:- ' . $lot_no . ' has been shared to IFMS successfully.',
                        'type' => 'green'
                    ];
                } else {
                    DB::connection('pgsql_paywrite')->rollback();
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
            try {
                DB::connection('pgsql_paywrite')->table('ifms.error_log')->insert([
                    'exception'  => $e->getMessage(),
                    'created_at' => date("Y-m-d H:i:s")
                ]);
            } catch (\Exception $logE) {
                // Ignore log insertion errors if table doesn't exist yet
            }
            
            return [
                'status' => 0,
                'msg' => 'Oops. Error occurred: ' . $e->getMessage(),
                'type' => 'red'
            ];
        }
    }
}
