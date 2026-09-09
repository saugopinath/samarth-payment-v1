<?php

namespace App\Services\Integration\IFMS;

use App\Models\PaymentLotMaster;
use App\Models\PaymentLotDetail;
use App\Models\IfmsTransactionLotDetail;
use App\Models\IfmsPaymentLotMasterAdditionalInfo;
use App\Models\PaymentMainSetting;
use App\Models\Scheme;
use App\Models\Codemaster;
use App\Models\FailedPaymentDetail;
use App\Services\Contracts\PaymentSBIIntegrationInterface;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class IfmsSftpIntegrationService implements PaymentSBIIntegrationInterface
{
    private static $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function preparePayload(PaymentLotMaster $lotMaster): bool
    {
        // TODO: Implement IFMS SFTP payload generation
        return true;
    }

    public function pushToTarget(PaymentLotMaster $lotMaster): bool
    {
        set_time_limit(0);
        DB::connection('pgsql_payment')->beginTransaction();
        DB::connection('pgsql_ifms')->beginTransaction();
        try {
            $lotNo = $lotMaster->lot_no;
            $schemeId = $lotMaster->scheme_id;
            
            $financialYear = $lotMaster->lot_year;
            $lotMonth = $lotMaster->lot_month;

            $setting = PaymentMainSetting::where('scheme_id', $schemeId)
                ->where('financial_year', $financialYear)
                ->first();

            $ddocode = '';
            $partyCode = '';
            if ($setting) {
                $monthField = strtolower($lotMonth);
                $monthData = $setting->$monthField;
                if (is_array($monthData)) {
                    $ddocode = $monthData['ddo_code'] ?? '';
                    $partyCode = $monthData['party_code'] ?? '';
                }
            }

            $schemeDetail = Scheme::find($schemeId);
            $schemeName = $schemeDetail ? ($schemeDetail->display_name ?? $schemeDetail->name) : '';

            $mytime = Carbon::now();
            $dateDate = $mytime->format('d');
            $dateMonth = $mytime->format('m');
            $dateYear = $mytime->format('Y');

            $serialNo = '01'; // Can be made dynamic if needed

            $filename = $ddocode . $partyCode . $dateDate . $dateMonth . $dateYear . $serialNo . $lotNo;
            $drnFull = $dateYear . $dateMonth . $partyCode . $lotNo;
            $fileNameFromDb = $lotMaster->file_name;

            $benData = IfmsTransactionLotDetail::where('lot_no', $lotNo)
                ->where('scheme_id', $schemeId)
                ->get();

            $xmlFile = $this->generatePushXml($drnFull, $benData, $schemeName);
            
            $pushedXmlPath = config('ifms.local_paths.pushed');
            $createXmlFilePath = storage_path("{$pushedXmlPath}/{$partyCode}/{$filename}.xml");
            if (!file_exists(dirname($createXmlFilePath))) {
                mkdir(dirname($createXmlFilePath), 0775, true);
            }
            
            $xmlString = $xmlFile->saveXML($xmlFile->documentElement);
            $xmlFile->save($createXmlFilePath);
            $pushedPath = config('ifms.local_paths.pushed');
            if (!Storage::exists("{$pushedPath}/{$partyCode}")) {
                Storage::makeDirectory("{$pushedPath}/{$partyCode}");
            }
            $isPutLocal = Storage::put("{$pushedPath}/{$partyCode}/{$filename}.xml", $xmlString);

            if ($isPutLocal) {
                if (app()->environment('local')) {
                    $this->simulateLocalEnvironment($partyCode, $filename, $fileNameFromDb, $xmlString, $drnFull, $benData);
                }
                
                Storage::disk("ifms_sftp_{$partyCode}")->put(config('ifms.paths.xmlpush') . '/' . $filename . '.xml', $xmlString);

                $exists = Storage::disk("ifms_sftp_{$partyCode}")->exists(config('ifms.paths.xmlpush') . '/' . $filename . '.xml');
                
                if ($exists) {
                    $lotMaster->file_name = $filename;
                    $lotMaster->cur_status = config('payment_lot.status.common.push');
                    $lotMaster->payment_push_date = now();
                    $is_save_main = $lotMaster->save();

                    $is_save_add = IfmsPaymentLotMasterAdditionalInfo::updateOrCreate(
                        [
                            'lot_no' => $lotMaster->lot_no,
                            'scheme_id' => $lotMaster->scheme_id,
                            'lot_year' => $lotMaster->lot_year,
                        ]
                    );
                    
                    if ($is_save_main && $is_save_add) {
                        DB::connection('pgsql_payment')->commit();
                        DB::connection('pgsql_ifms')->commit();
                        return true;
                    } else {
                        DB::connection('pgsql_payment')->rollback();
                        DB::connection('pgsql_ifms')->rollback();
                        return false;
                    }
                }
            } else {
                Log::error("Failed to write XML file to local storage: {$pushedPath}/{$partyCode}/{$filename}.xml");
            }

            DB::connection('pgsql_payment')->rollback();
            DB::connection('pgsql_ifms')->rollback();
            return false;
        } catch (Exception $e) {
            DB::connection('pgsql_payment')->rollback();
                      DB::connection('pgsql_ifms')->rollback();
            Log::error("Exception in pushToTarget: " . $e->getMessage());
            return false;
        }
    }

    public function checkdotDone(PaymentLotMaster $lotMaster): array
    {
       // dd('ok');
        set_time_limit(0);
        try { 
            $lotNo = $lotMaster->lot_no;
            $schemeId = $lotMaster->scheme_id;
            
            $financialYear = $lotMaster->lot_year;
            $lotMonth = $lotMaster->lot_month;

            $setting = PaymentMainSetting::where('scheme_id', $schemeId)
                ->where('financial_year', $financialYear)
                ->first();

            $ddocode = '';
            $partyCode = '';
            if ($setting) {
                $monthField = strtolower($lotMonth);
                $monthData = $setting->$monthField;
                if (is_array($monthData)) {
                    $ddocode = $monthData['ddo_code'] ?? '';
                    $partyCode = $monthData['party_code'] ?? '';
                }
            }
            $lotNo = $lotMaster->lot_no;  
            $fileName = $lotMaster->file_name;      	
            //dump(config('ifms.paths.dotdone'));dump($partyCode);dd( $fileName);	     

            $exists = Storage::disk("ifms_sftp_{$partyCode}")->exists(config('ifms.paths.dotdone') . '/' . $fileName . '.xml.done');
            if ($exists) {
                $remoteFile = Storage::disk("ifms_sftp_{$partyCode}")->get(config('ifms.paths.dotdone') . '/' . $fileName . '.xml.done');
                $dotdonePath = config('ifms.local_paths.dotdone');
                
                $localDotdoneFilePath = storage_path("{$dotdonePath}/{$partyCode}/{$fileName}.xml.done");
                if (!file_exists(dirname($localDotdoneFilePath))) {
                    mkdir(dirname($localDotdoneFilePath), 0775, true);
                }
                file_put_contents($localDotdoneFilePath, $remoteFile);

                if (!Storage::exists("{$dotdonePath}/{$partyCode}")) {
                    Storage::makeDirectory("{$dotdonePath}/{$partyCode}");
                }
                if(!Storage::put("{$dotdonePath}/{$partyCode}/{$fileName}.xml.done", $remoteFile)){
                    return [
                        'status' => 2, 
                        'msg' => 'Dotdone file is not available for lot no. ' . $lotNo . '.',
                        'type' => 'red', 
                        'icon' => 'fa fa-check', 
                        'title' => 'Failed'
                    ];
                }

                $lotMaster->cur_status = config('payment_lot.status.ifms.dotdone'); // GENERATED, PUSHED AND RESPONSE RECEIVED
                if($lotMaster->save()){
                
                return [
                    'status' => 1, 
                    'msg' => 'Lot no. ' . $lotNo . ' successfully verified dotDone status.',
                    'type' => 'green', 
                    'icon' => 'fa fa-check', 
                    'title' => 'Success'
                ];
            }
            else{
                return [
                    'status' => 2, 
                    'msg' => 'Lot no. ' . $lotNo . ' could not be verified dotDone status.',
                    'type' => 'red', 
                    'icon' => 'fa fa-check', 
                    'title' => 'Failed'
                ];
            }
            } else {
                return [
                    'status' => 3, 
                    'msg' => 'Lot no. ' . $lotNo . ' has not yet been received by IFMS.',
                    'type' => 'orange', 
                    'icon' => 'fa fa-info', 
                    'title' => 'Not Received'
                ];
            }
        } catch (Exception $e) {
            Log::error("Exception in checkdotDone: " . $e->getMessage());
            return [
                'status' => 2,
                'msg' => 'Exception: ' . $e->getMessage(),
                'type' => 'red',
                'icon' => 'fa fa-warning',
                'title' => 'Error'
            ];
        }
    }

    public function checkAcknowledge(PaymentLotMaster $lotMaster): array
    {
        try {
            $lotNo = $lotMaster->lot_no;
            $schemeId = $lotMaster->scheme_id;
            
            $financialYear = $lotMaster->lot_year;
            $lotMonth = $lotMaster->lot_month;

            $setting = PaymentMainSetting::where('scheme_id', $schemeId)
                ->where('financial_year', $financialYear)
                ->first();

            $ddocode = '';
            $partyCode = '';
            if ($setting) {
                $monthField = strtolower($lotMonth);
                $monthData = $setting->$monthField;
                if (is_array($monthData)) {
                    $ddocode = $monthData['ddo_code'] ?? '';
                    $partyCode = $monthData['party_code'] ?? '';
                }
            }
            $lotNo = $lotMaster->lot_no;  
            $fileName = $lotMaster->file_name;
          
            if ($lotMaster->cur_status != config('payment_lot.status.ifms.dotdone')) {
                    return [
                        'status' => 3, 
                        'msg' => 'Bill reference no is already generated.',
                        'type' => 'orange', 
                        'icon' => 'fa fa-info', 
                        'title' => 'Complete'
                    ];
            }
            $fileName = 'ACK' . $fileName;
            if (!str_ends_with($fileName, '.xml')) {
                $fileName .= '.xml';
            }
            //dd($fileName);
            //dd(config('ifms.paths.ack'));
            $exists = Storage::disk("ifms_sftp_{$partyCode}")->exists(config('ifms.paths.ack') .$fileName);
            //dd($exists);
            if ($exists) {
                if ($lotMaster->cur_status == config('payment_lot.status.common.ack')) {
                    return [
                        'status' => 3, 
                        'msg' => 'Bill reference no is already generated.',
                        'type' => 'orange', 
                        'icon' => 'fa fa-info', 
                        'title' => 'Complete'
                    ];
                }
                
                $remoteFile = Storage::disk("ifms_sftp_{$partyCode}")->get(config('ifms.paths.ack') .$fileName);
                $ackPath = config('ifms.local_paths.ack');
                
                $localAckFilePath = storage_path("{$ackPath}/{$partyCode}/{$fileName}");
                if (!file_exists(dirname($localAckFilePath))) {
                    mkdir(dirname($localAckFilePath), 0775, true);
                }
                file_put_contents($localAckFilePath, $remoteFile);

                if (!Storage::exists("{$ackPath}/{$partyCode}")) {
                    Storage::makeDirectory("{$ackPath}/{$partyCode}");
                }
                if(!Storage::put("{$ackPath}/{$partyCode}/{$fileName}", $remoteFile)){
                    return [
                        'status' => 2, 
                        'msg' => 'Ack file is not available for lot no. ' . $lotNo . '.',
                        'type' => 'red', 
                        'icon' => 'fa fa-check', 
                        'title' => 'Failed'
                    ];
                }
                $remoteXmlFile = simplexml_load_string($remoteFile);

                $ifmsRefNo = (string)$remoteXmlFile->IFMS_REF_NO;
               // dd($ifmsRefNo);
                DB::connection('pgsql_payment')->beginTransaction();
                DB::connection('pgsql_ifms')->beginTransaction();

                try {
                    $lotMaster->cur_status = config('payment_lot.status.common.ack');
                    $is_save_main = $lotMaster->save();
                    
                    $lotInfo = \App\Models\IfmsPaymentLotMasterAdditionalInfo::where('lot_no', $lotNo)->firstOrFail();
                    $lotInfo->ref_no = $ifmsRefNo;
                    $is_save_add = $lotInfo->save();

                    if ($is_save_main && $is_save_add) {
                        DB::connection('pgsql_payment')->commit();
                        DB::connection('pgsql_ifms')->commit();
                    } else {
                        DB::connection('pgsql_payment')->rollback();
                        DB::connection('pgsql_ifms')->rollback();
                        return [
                            'status' => 2, 
                            'msg' => 'Database update failed while saving Ack details.',
                            'type' => 'red', 
                            'icon' => 'fa fa-warning', 
                            'title' => 'Error'
                        ];
                    }
                } catch (Exception $ex) {
                    DB::connection('pgsql_payment')->rollback();
                    DB::connection('pgsql_ifms')->rollback();
                    Log::error("Database transaction failed in checkAcknowledge: " . $ex->getMessage());
                    return [
                        'status' => 2, 
                        'msg' => 'Exception during database update: ' . $ex->getMessage(),
                        'type' => 'red', 
                        'icon' => 'fa fa-warning', 
                        'title' => 'Error'
                    ];
                }
                //$wrongFileStatus = $this->wrong_file_status($partyCode, $fileName, $schemeId, $lotNo);
                //dd($wrongFileStatus);
                $wrongFileStatus='1';
                if ($wrongFileStatus) {
                    return [
                        'status' => 1, 
                        'msg' => "Reference No. {$ifmsRefNo} generated for Lot no. {$lotNo}. {$wrongFileStatus}",
                        'type' => 'green', 
                        'icon' => 'fa fa-check', 
                        'title' => 'Success'
                    ];
                } else {
                    return [
                        'status' => 2, 
                        'msg' => 'Unable to check wrongdata.',
                        'type' => 'red', 
                        'icon' => 'fa fa-warning', 
                        'title' => 'Error'
                    ];
                }
            } 
            else{
            // Check Error File if ACK doesn't exist
            $list = Storage::disk("ifms_sftp_{$partyCode}")->files(config('ifms.paths.wrong'));
            $matches = preg_grep('/^' . preg_quote(config('ifms.paths.wrong') . '/' . $fileName, '/') . '/', $list);
            
            if (!empty($matches)) {
                $errorFileName = end($matches);
                if (Storage::disk("ifms_sftp_{$partyCode}")->exists($errorFileName)) {
                    $remoteErrorFile = Storage::disk("ifms_sftp_{$partyCode}")->get($errorFileName);
                    if (!empty($remoteErrorFile)) {
                        $countBeneficiary = 0;
                        if (strpos($errorFileName, 'D_') !== false) {
                            $remoteXmlFileData = simplexml_load_string($remoteErrorFile);
                            foreach ($remoteXmlFileData as $node) {
                                $countBeneficiary++;
                            }
                        }
                        
                        $beneficiaryCountFromDb = PaymentLotDetail::where('lot_no', $lotNo)
                            ->where('scheme_id', $schemeId)->count();
                            
                        if ($beneficiaryCountFromDb == $countBeneficiary) {
                            $wrongFileStatus = $this->wrong_file_status($partyCode, $errorFileName, $schemeId, $lotNo);
                            if ($wrongFileStatus) {
                                return [
                                    'status' => 4, 
                                    'msg' => "Reference No. not generated for Lot no. {$lotNo} as all beneficiaries failed at IFMS. {$wrongFileStatus}",
                                    'type' => 'red', 
                                    'icon' => 'fa fa-warning', 
                                    'title' => 'Error'
                                ];
                            } else {
                                return [
                                    'status' => 5, 
                                    'msg' => 'Unable to check wrong data.',
                                    'type' => 'red', 
                                    'icon' => 'fa fa-warning', 
                                    'title' => 'Error'
                                ];
                            }
                        } else {
                            return [
                                'status' => 6, 
                                'msg' => 'IFMS Reference Not Generated.',
                                'type' => 'red', 
                                'icon' => 'fa fa-warning', 
                                'title' => 'Error'
                            ];
                        }
                    } else {
                        return [
                            'status' => 7, 
                            'msg' => 'System Error. Remote file is empty.',
                            'type' => 'red', 
                            'icon' => 'fa fa-warning', 
                            'title' => 'Error'
                        ];
                    }
                } 
            }else {
					return array(
						'status' => 8, 'msg' => 'No Acknowledgement, No Error File.',
						'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
					);
				}
        }
            
            return [
                'status' => 8, 
                'msg' => 'No Acknowledgement, No Error File.',
                'type' => 'red', 
                'icon' => 'fa fa-warning', 
                'title' => 'Error'
            ];
            
        } catch (Exception $e) {
           // dd($e->getMessage());
            Log::error("Exception in checkAcknowledge: " . $e->getMessage());
            return [
                'status' => 2,
                'msg' => 'Exception: ' . $e->getMessage(),
                'type' => 'red',
                'icon' => 'fa fa-warning',
                'title' => 'Error'
            ];
        }
    }
  
    public function checkResponse(PaymentLotMaster $lotMaster): array
    {
        try {
             $lotNo = $lotMaster->lot_no;
            $schemeId = $lotMaster->scheme_id;
            
            $financialYear = $lotMaster->lot_year;
            $lotMonth = $lotMaster->lot_month;

            $setting = PaymentMainSetting::where('scheme_id', $schemeId)
                ->where('financial_year', $financialYear)
                ->first();

            $ddocode = '';
            $partyCode = '';
            if ($setting) {
                $monthField = strtolower($lotMonth);
                $monthData = $setting->$monthField;
                if (is_array($monthData)) {
                    $ddocode = $monthData['ddo_code'] ?? '';
                    $partyCode = $monthData['party_code'] ?? '';
                }
            }
            $lotNo = $lotMaster->lot_no;  
            $baseFileName = preg_replace('/\.xml$/i', '', $lotMaster->file_name);      
            
           if ($lotMaster->cur_status == config('payment_lot.status.common.response')) {
                    return [
                        'status' => 3, 
                        'msg' => 'Response is already generated.',
                        'type' => 'orange', 
                        'icon' => 'fa fa-info', 
                        'title' => 'Complete'
                    ];
                }

            $list = Storage::disk("ifms_sftp_{$partyCode}")->files(config('ifms.paths.response'));
            $matchingFiles = preg_grep('/^' . preg_quote(config('ifms.paths.response') . '/' . $partyCode . $baseFileName, '/') . '/', $list);
            
            if (!$matchingFiles) {
                return [
                    'status' => 2,
                    'msg' => 'Response file not found.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Error'
                ];
            }
            
            $matchedFilename = end($matchingFiles);
            $fileName1 = basename($matchedFilename);
            
            $codemasterChilds = collect(config('codemaster.childs'));
            $failedTypeCode = $codemasterChilds->where('short_name', 'payment_failed')->first()['code'] ?? '1415';
            $sbiSourceCode = $codemasterChilds->where('short_name', 'ifms')->first()['code'] ?? '5202';
            
            $remoteFile = Storage::disk("ifms_sftp_{$partyCode}")->get($matchedFilename);
            $rbiRespPath = config('ifms.local_paths.rbi_resp');
            
            $localRbiRespFilePath = storage_path("{$rbiRespPath}/{$partyCode}/{$fileName1}");
            if (!file_exists(dirname($localRbiRespFilePath))) {
                mkdir(dirname($localRbiRespFilePath), 0775, true);
            }
            file_put_contents($localRbiRespFilePath, $remoteFile);

            if (!Storage::exists("{$rbiRespPath}/{$partyCode}")) {
                Storage::makeDirectory("{$rbiRespPath}/{$partyCode}");
            }
           if(!Storage::put("{$rbiRespPath}/{$partyCode}/{$fileName1}", $remoteFile)){
            return [
                'status' => 2, 
                'msg' => 'Rbi response file is not available for lot no. ' . $lotNo . '.',
                'type' => 'red', 
                'icon' => 'fa fa-check', 
                'title' => 'Failed'
            ];
           }
            
            $remoteXmlFile = simplexml_load_string($remoteFile);	
            $voucherNo = (string)$remoteXmlFile->voucherNo;
            $voucherDate = (string)$remoteXmlFile->voucherDate;
            $tokenNo = (string)$remoteXmlFile->tokenNo;
            $tokenDate = (string)$remoteXmlFile->tokenDate;
            
            $successCount = 0;
            $failedCount = 0;
            $successAmount = 0;
            $failedAmount = 0;
            
            DB::connection('pgsql_payment')->beginTransaction();
            DB::connection('pgsql_ifms')->beginTransaction();
            $failed_update=0;
            $success_update=0;

            try {
                foreach ($remoteXmlFile->beneficiaryDetail as $detailXml) {
                    // In original code, it looped through XML structure in a nested array logic. 
                    // Using simplexml properly:
                    $status = (string)$detailXml->status;
                    $refBenfId = (string)$detailXml->refBenfId;
                    $utrNo = (string)$detailXml->utrNo;
                    $reason = (string)$detailXml->reason;
                    $amount = (string)$detailXml->amount;
                    if ($status == 'Success') {
                        $successCount++;
                         $successAmount += $amount;
                    } elseif ($status == 'Failed') {
                        $failedCount++;
                         $failedAmount += $amount;
                        $codemasterStatus = Codemaster::where('short_name', $status)
                            ->where('parent_short_code', 'ifms_status_code')
                            ->first();
                        $mappedStatusCode = $codemasterStatus ? $codemasterStatus->code : 'FAILED';
                        
                        if(FailedPaymentDetail::create([
                            'lot_no' => $lotMaster->lot_no,
                            'ben_id' => $refBenfId,
                            'scheme_id' => $schemeId,
                            'status_code' => $mappedStatusCode,
                            'remarks' => $reason,
                            'failed_type' => $failedTypeCode,
                            'failed_source' => $sbiSourceCode
                        ])){
                            $failed_update++;
                        }
                    }
                    
                    $detail = IfmsTransactionLotDetail::where('lot_no', $lotMaster->lot_no)
                        ->where('scheme_id', $schemeId)
                        ->where('ben_id', $refBenfId)
                        ->first();

                    if ($detail) {
                        $codemasterStatus = Codemaster::where('short_name', $status)
                            ->where('parent_short_code', 'ifms_status_code')
                            ->first();
                        // Original code mapped `$credit_status` which was undefined. Fallback to `$status`
                        $mappedStatusCode = $codemasterStatus ? $codemasterStatus->code : $status; 

                        $detail->utr_no = $utrNo;
                       
                        if($detail->save()){
                            $success_update++;
                        }
                    }
                }
                $lotMaster->success_count = $successCount;
                $lotMaster->failed_count = $failedCount;
                $lotMaster->success_amount = $successAmount;
                $lotMaster->failed_amount = $failedAmount;
                $lotMaster->cur_status = config('payment_lot.status.common.response');
                $is_save_main = $lotMaster->save();

                $lotInfo = \App\Models\IfmsPaymentLotMasterAdditionalInfo::where('lot_no', $lotNo)->firstOrFail();
                $lotInfo->voucher_no = $voucherNo;
                $lotInfo->voucher_date = $voucherDate;
                $lotInfo->token_no = $tokenNo;
                $lotInfo->token_date = $tokenDate;
                $is_save_add = $lotInfo->save();

                if ($is_save_main && $is_save_add && (($failed_update+$success_update) == ($failedCount+$successCount))) {
                    DB::connection('pgsql_payment')->commit();
                    DB::connection('pgsql_ifms')->commit();
                    return [
                        'status' => 1, 'msg' => 'RBI Report Imported Successfully for Lot No. ' . $lotNo . '.',
                        'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success'
                    ];
                } else {
                    DB::connection('pgsql_payment')->rollback();
                    DB::connection('pgsql_ifms')->rollback();
                    return [
                        'status' => 2, 
                        'msg' => 'Database update failed while saving RBI response details.',
                        'type' => 'red', 
                        'icon' => 'fa fa-warning', 
                        'title' => 'Error'
                    ];
                }
            } catch (Exception $ex) {
                DB::connection('pgsql_payment')->rollback();
                DB::connection('pgsql_ifms')->rollback();
                Log::error("Database transaction failed in checkResponse: " . $ex->getMessage());
                return [
                    'status' => 2, 
                    'msg' => 'Exception during database update: ' . $ex->getMessage(),
                    'type' => 'red', 
                    'icon' => 'fa fa-warning', 
                    'title' => 'Error'
                ];
            }
        } catch (Exception $e) {
            Log::error("Exception in checkResponse: " . $e->getMessage());
            return [
                'status' => 2,
                'msg' => 'Exception: ' . $e->getMessage(),
                'type' => 'red',
                'icon' => 'fa fa-warning',
                'title' => 'Error'
            ];
        }
    }

    public function wrong_file_status(string $partyCode, string $fileName, int $schemeId, string $lotNo): string
    {
        try {
            
            
            $list = Storage::disk("ifms_sftp_{$partyCode}")->files(config('ifms.paths.wrong'));
            $matchingFiles = preg_grep('/^' . preg_quote(config('ifms.paths.wrong') . '/' . $fileName, '/') . '/', $list);
            
            $matchedFilename = !empty($matchingFiles) ? end($matchingFiles) : '';

            if (!$matchedFilename || !Storage::disk("ifms_sftp_{$partyCode}")->exists($matchedFilename)) {
                return 'No Wrong Data File Received';
            }
            
            $remoteFile = Storage::disk("ifms_sftp_{$partyCode}")->get($matchedFilename);
            $fileName1 = basename($matchedFilename);
            $ifmsRespPath = config('ifms.local_paths.ifms_resp');
            
            $localIfmsRespFilePath = storage_path("{$ifmsRespPath}/{$partyCode}/{$fileName1}");
            if (!file_exists(dirname($localIfmsRespFilePath))) {
                mkdir(dirname($localIfmsRespFilePath), 0775, true);
            }
            file_put_contents($localIfmsRespFilePath, $remoteFile);

            if (!Storage::exists("{$ifmsRespPath}/{$partyCode}")) {
                Storage::makeDirectory("{$ifmsRespPath}/{$partyCode}");
            }
            Storage::put("{$ifmsRespPath}/{$partyCode}/{$fileName1}", $remoteFile);
            $remoteXmlFile = simplexml_load_string($remoteFile);

            if (strpos($matchedFilename, 'D_') !== false) {   
                $wrongDataCount = 0;
                foreach ($remoteXmlFile as $errorDetail) {
                    $errorArray = (array)$errorDetail;
                    $errorValues = array_values($errorArray);
                    
                    // Fallback indexes based on original undocumented mapping. Ideally should use object properties.
                    $id = $errorValues[7] ?? null;
                    $errorReason = $errorValues[10] ?? null;

                    if ($id && $errorReason) {
                        DB::connection('pgsql_paywrite')->table('ifms.ifms_return_details')->insert([
                            'file_name' => $matchedFilename, 
                            'drn_part' => $drnPart, 
                            'scheme_id' => $schemeId, 
                            'ben_id' => $id, 
                            'error_reason' => $errorReason
                        ]);

                        $pensionidArray = DB::connection('pgsql_paywrite')->table('ifms.transaction_lot_details')
                            ->select('pension_id')
                            ->where('ben_id', $id)
                            ->where('drn_part', $drnPart)
                            ->where('scheme_id', $schemeId)
                            ->first();

                        if ($pensionidArray) {
                            $pensionId = $pensionidArray->pension_id;
                            DB::connection('pgsql_paywrite')->table('ifms.transaction_lot_details')
                                ->where('pension_id', $pensionId)
                                ->where('drn_part', $drnPart)
                                ->where('scheme_id', $schemeId)
                                ->update([
                                    'ifms_status' => $errorReason, 
                                    'ifms_ref_no' => '0', 
                                    'wrongdata_flag' => 1, 
                                    'updated_at' => DB::raw("now()"), 
                                    'status_code' => 'IF', 
                                    'pmt_status' => false
                                ]);
                        }
                    }
                    $wrongDataCount++;
                }

                DB::connection('pgsql_paywrite')->table('payment.lot_master')
                    ->where('lot_no', $drnPart)->where('scheme_id', $schemeId)->update(['ifms_wrongdata_count' => $wrongDataCount]);
                DB::connection('pgsql_paywrite')->table('ifms.transaction_lot')
                    ->where('lot_no', $drnPart)->where('scheme_id', $schemeId)->update(['ifms_wrongdata_count' => $wrongDataCount]);

                $returnStatus = "{$wrongDataCount} Wrong Data Collected";

            } else {
                $reason = (string)$remoteXmlFile->REASON;
                
                DB::connection('pgsql_paywrite')->table('payment.lot_master')
                    ->where('lot_no', $drnPart)->where('scheme_id', $schemeId)->update(['lot_status' => 1]);
                DB::connection('pgsql_paywrite')->table('ifms.transaction_lot')
                    ->where('lot_no', $drnPart)->where('scheme_id', $schemeId)->update(['lot_status' => 10]);
                DB::connection('pgsql_paywrite')->table('ifms.transaction_lot_details')
                    ->where('drn_part', $drnPart)->where('scheme_id', $schemeId)->where('is_active', '<>', 0)
                    ->update(['ifms_status' => $reason]);

                $returnStatus = $reason;
            }
            
        

            return $returnStatus ?: 'No Wrong Data File Received';
            
        } catch (Exception $e) {
            //dd($e);
            Log::error("Exception in wrong_file_status: " . $e->getMessage());
            return 'Exception during processing wrong file data.';
        }
    }

    private function generatePushXml(string $drnFull, $benData, string $schemeName): DOMDocument
    {
        $totalValue = $benData->sum('amount_rs');
        $benfCount = $benData->count();
        
        $xmlFile = new DOMDocument("1.0", 'UTF-8');
        $xmlFile->formatOutput = true;

        $bulkecs = $xmlFile->createElement("bulkecs");
        $xmlFile->appendChild($bulkecs);
        
        $drn = $xmlFile->createElement("DRN", $drnFull);
        $bulkecs->appendChild($drn);

        $bulkecs->setAttribute('totalamount', (string)$totalValue);
        $bulkecs->setAttribute('benfcount', (string)$benfCount);

        foreach ($benData as $details) {
            $beneficiary = $xmlFile->createElement("BENEFICIARY");
            $bulkecs->appendChild($beneficiary);

            $beneficiary->appendChild($xmlFile->createElement("BENF_NAME", substr(trim($details->ben_name ?? ''), 0, 99)));
            $beneficiary->appendChild($xmlFile->createElement("ACCOUNT_NO", trim($details->accno ?? '')));
            $beneficiary->appendChild($xmlFile->createElement("IFSC_CODE", trim($details->ifsc ?? '')));
            $beneficiary->appendChild($xmlFile->createElement("MOBILE_NO", trim($details->mobile_no ?? '')));
            $beneficiary->appendChild($xmlFile->createElement("AMOUNT", trim($details->amount_rs ?? '')));
            $beneficiary->appendChild($xmlFile->createElement("ID", trim($details->ben_id ?? '')));
            $beneficiary->appendChild($xmlFile->createElement("ORDER_NO", trim($details->order_no_date ?? '')));
            $beneficiary->appendChild($xmlFile->createElement("UNIQUE_ID", trim($details->unique_id ?? '')));
            $beneficiary->appendChild($xmlFile->createElement("REMARKS", trim($schemeName)));
        }

        return $xmlFile;
    }

    private function simulateLocalEnvironment($partyCode, $filename, $fileNameFromDb, $xmlString, $drnFull, $benData): void
    {
        try {
            $doneFileName = $filename . '.xml.done';
            Storage::disk("ifms_sftp_{$partyCode}")->put(config('ifms.paths.dotdone') . '/' . $doneFileName, '');
            $ackXmlString = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><Acknowledgement><FILENAME>' . $filename . '.xml</FILENAME><IFMS_REF_NO>' . date('YmdHis') . '</IFMS_REF_NO></Acknowledgement>';
            Storage::disk("ifms_sftp_{$partyCode}")->put(config('ifms.paths.ack') . '/ACK' . $filename . '.xml', $ackXmlString);
        } catch (Exception $e) {
            Log::error("Local simulation error: " . $e->getMessage());
        }
        
        $resXml = new DOMDocument("1.0", 'UTF-8');
        $resXml->xmlStandalone = true;
        $resXml->formatOutput = true;
        
        $paymentDetail = $resXml->createElement("paymentDetail");
        $resXml->appendChild($paymentDetail);
        
        $paymentDetail->appendChild($resXml->createElement("DRN", $drnFull));
        $paymentDetail->appendChild($resXml->createElement("voucherNo", "223573"));
        $paymentDetail->appendChild($resXml->createElement("voucherDate", "11/07/2026"));
        $paymentDetail->appendChild($resXml->createElement("tokenNo", "15584"));
        $paymentDetail->appendChild($resXml->createElement("tokenDate", "09/07/2026"));
        
        foreach ($benData as $details) {
            $benfDetail = $resXml->createElement("beneficiaryDetail");
            $paymentDetail->appendChild($benfDetail);
            
            $benfDetail->appendChild($resXml->createElement("accountNumber", trim($details->accno ?? '')));
            $benfDetail->appendChild($resXml->createElement("amount", trim($details->amount_rs ?? '')));
            $benfDetail->appendChild($resXml->createElement("fileName", $filename . '.xml'));
            $benfDetail->appendChild($resXml->createElement("ifscCode", trim($details->ifsc ?? '')));
            $benfDetail->appendChild($resXml->createElement("paymentDt", "15/07/2026"));
            $benfDetail->appendChild($resXml->createElement("reason", ""));
            $benfDetail->appendChild($resXml->createElement("refBenfId", trim($details->ben_id ?? '')));
            $benfDetail->appendChild($resXml->createElement("refOrdernoDt", ""));
            $benfDetail->appendChild($resXml->createElement("referenceNo", trim($details->unique_id ?? '')));
            $benfDetail->appendChild($resXml->createElement("status", "Success"));
            $benfDetail->appendChild($resXml->createElement("utrNo", "123"));
        }
        
        $resXmlString = $resXml->saveXML();
        $baseName = preg_replace('/\.xml$/i', '', $fileNameFromDb ?: $filename);
        $responseFileName = $partyCode . $baseName . '_00234.xml';
        Storage::disk("ifms_sftp_{$partyCode}")->put(config('ifms.paths.response') . '/' . $responseFileName, $resXmlString);
    }
}
