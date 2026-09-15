<?php

namespace App\Http\Controllers;
use App\Helpers\BandhanPayment;
use App\Helpers\Helper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PushToBandhanController extends Controller
{
    use SendsPasswordResetEmails;
    public function __construct()
    {
        // $this->middleware('auth');
        //$this->middleware('Admin');
        // $this->middleware(['auth','MaintainMiddleware']);
        date_default_timezone_set('Asia/Kolkata');
        set_time_limit(500);
        //ini_set('memory_limit', '128M');
    }
   


    public function LBUploadTransactionFile(Request $request)
    { //echo 1;die;
        // dd($request->all());
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in ajax call.');
            return response()->json($response, $statusCode);
        }
        try {
            date_default_timezone_set('Asia/Kolkata');
            $finYear = $request->year;
            $designation_id = Auth::user()->designation_id;
            $created_by = Auth::user()->id;
            $ip_address = $request->ip();
            // $getModelFunc = new getModelFunc();
            // $schemaname = $getModelFunc->getSchemaDetails($finYear);

            // $schemaname = 'trx_mgmt_cur_fy';   // For fin year change

            // new for previous year arrear payment Date - 26-03-2023
            $schemaname = DB::connection('pgsql_payment')->table('master_mgmt.m_financial_master')->where('financial_year', $finYear)->value('db_schema_name');
            // 
            // echo $schemaname;
            // die;
            $lotNo = base64_decode($request->lot_no);
            $op_type = 'BANDHANPAYLOTPUSH';
            // $insertLog = Helper::insertLogAcceptRejectInfo($op_type, null,$lotNo, null, null, $ip_address, $designation_id, $created_by);
            $lot_info = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $finYear)->first();
            $insertLog = [];
            $insertLog['created_by'] = $created_by;
            $insertLog['created_by_dist_code'] = $lot_info->dist_code;
            $insertLog['created_at'] = date('Y-m-d H:i:s');
            $insertLog['op_type'] = $op_type;
            $insertLog['ip_address'] = $ip_address;
            $insertLog['designation_id'] = $designation_id;
            $insertLog['lot_no'] = $lotNo;
            $insertLog['lot_year'] = $finYear;

            $benAcceptRejectInfo = DB::connection('pgsql_payment')->table('public.ben_accept_reject_info')->insert($insertLog);
            // dd($lot_info);
            $updateProcessingLotStatus = DB::connection('pgsql_payment')->table('bandhan.lot_master')
                ->where('lot_no', $lotNo)
                ->where('lot_year', $finYear)
                ->where('lot_status', '0')
                ->update([
                    'lot_status' => '8'
                ]);
            $logmessage = "";
            $logmessage .= date('l d-M-Y h:i:s A') . "\n";
            $logmessage .= "------------------------------" . "\n";
            $logFileName = $lot_info->file_name;
            $fileLocation = 'bandhanlog/' . $logFileName . '_TranUpload' . '.txt';
            $monthcount = $lot_info->month_count;
            $distCode = $lot_info->dist_code;
            $lotmonth = $lot_info->lot_month;
            $loopcount = $lotmonth + $monthcount;

            $count = $lotmonth;


            $setquery = '';
            //  $bendata = LotDetails::on('pgsql_payment')->where('lot_no', $lotNo)->get();
            // $querySelect = "select ld_id, ben_id, amount_rs,regexp_replace(ben_name, '''', '', 'g') as ben_name,ifsc,accno from ".$schemaname.".lot_details where lot_no=".$lotNo." and dist_code=".$distCode;

            $querySelect = "select transaction_text from bandhan.lot_master where lot_no=" . $lotNo . " and  dist_code=" . $distCode;
            // echo $querySelect;
            // die;
            $output = array();
            $bendata = DB::connection('pgsql_payment')->select($querySelect);
            // print_r( $bendata);die;
            $record_text = $bendata[0]->transaction_text;
            if (!empty($record_text)) {
                $output[] = $bendata[0]->transaction_text;
            } else {
                $update_text = DB::connection('pgsql_payment')->select("select " . $schemaname . ".transaction_data_update(" . $lotNo . ", " . $distCode . ")");
                $result_text = DB::connection('pgsql_payment')->select($querySelect);
                $output[] = $result_text[0]->transaction_text;
            }
            // dd($output);
            // foreach ($bendata as $valbendata) {  
            //     $transaction_id = $valbendata->ld_id;
            //     $amount_rs = trim($valbendata->amount_rs);
            //     $ben_name = trim($valbendata->ben_name);
            //     $ifsc = trim($valbendata->ifsc);
            //     $accno = trim($valbendata->accno);
            //     $uniqueId = $valbendata->ben_id;
            //     $output[] = $transaction_id . '|'  . $uniqueId . '|' .  $amount_rs .'|' .  $ben_name .'|'.  $accno .'|' .  $ifsc . "\n" . '';

            // }
            //echo 1;die;
            // $filename = $lot_info->file_name . '_Push' . '.txt';

            // if (Storage::disk('bandhantransaction')->exists($filename)) {
            //     Storage::disk('bandhantransaction')->delete($filename);
            // }
            // $savefile = Storage::disk('bandhantransaction')->put($filename, implode("", $output));
            // dd($isFileExists);
            // Storage::disk('bandhantransaction')->put($filename, $output);
            // dd($savefile);
            $diskPath = storage_path('app/bandhantransaction');
            if (! File::exists($diskPath)) {
                File::makeDirectory($diskPath, 0755, true);
            }

            $filename = $lot_info->file_name . '_Push.txt';
            // dd($filename);
            $savefile = Storage::disk('bandhantransaction')->put($filename, implode("", $output));
            // dd( $savefile);
            if ($savefile) {
                // dd('ok');
                $logmessage .= 'File Save Successfully' . "\n";
                $key = Config::get('bandhan.EncryptionKey');
                $iv = Config::get('bandhan.IvData');
                $data = Storage::disk('bandhantransaction')->get($filename);
                $compressed = gzcompress($data, 9);
                $base64OfCompressedData = base64_encode($compressed);
                $encode_encrypted_data = BandhanPayment::encryptCode($key, $iv, $base64OfCompressedData);
                $logmessage .= "Encrypted successfully" . "\n";

                $TriggeredByUserId = config('bandhan.TriggeredByUserId');
                $ApplicationId = config('bandhan.ApplicationId');
                $LotTransactionUploadActionId = config('bandhan.LotTransactionUploadActionId');

                $apiFieldArray = ['@recordCount', '@lotNumber', '@data', '@year', '@month'];
                $recordCount = $lot_info->ben_count;
                $lotNumber = $lot_info->file_name;
                $stringValue = $encode_encrypted_data;
                $lotYear = $lot_info->lot_year;
                $lotMonth = $lot_info->lot_month;
                $explodeYear = explode("-", $lotYear);
                // $year = $explodeYear[0];
                if ($lotMonth <= 3) {
                    $year = trim($explodeYear[1]);
                } else {
                    $year = trim($explodeYear[0]);
                }


                $apiFieldValueArray = array('0' => $recordCount, '1' => $lotNumber, '2' => $stringValue, '3' => $year, '4' => $lotMonth);
                // dd($apiFieldValueArray);
                // dd($apiFieldValueArray, $TriggeredByUserId, $ApplicationId, $LotTransactionUploadActionId);
                $apiResult = BandhanPayment::curlPayment($TriggeredByUserId, $ApplicationId, $LotTransactionUploadActionId, $apiFieldArray, $apiFieldValueArray);
                // print_r($apiResult);
                // die;
                $logmessage .= "Fetching curl successfully" . "\n";
                if ($apiResult['errorCurl']) {
                    // dd('errorCurl');
                    $updateLotStatus = DB::connection('pgsql_payment')->table('bandhan.lot_master')
                        ->where('lot_no', $lotNo)
                        ->where('lot_year', $finYear)
                        ->where('lot_status', '8')
                        ->update([
                            'lot_status' => '0'
                        ]);
                    $logmessage .= "Curl Error Message:- " . $apiResult['errorCurl'] . "\n";
                    $response = array(
                        'status' => 4,
                        'msg' => $apiResult['errorCurl'],
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Error'
                    );
                } else {
                    // dd('decodeResponse');
                    $decodeResponse = json_decode(json_decode($apiResult['result'], TRUE));
                    // dd($decodeResponse);
                    $responseStatus = $decodeResponse->ResponseStatus;
                    // dd($responseStatus);
                    $errorMessage = $decodeResponse->ErrorMessage;
                    $logmessage .= "Response Status:- " . $responseStatus . "\n";
                    // dd($responseStatus);
                    if ($responseStatus === 'FAILURE') {

                        if ($errorMessage == 'This lot number is already exists') {
                            // dd('ok1');
                            // already pushed previously
                            $logmessage .= "Succesfully Response Received:- " . $errorMessage . "\n";
                            // for ($i = $lotmonth; $i < $loopcount; $i++) {
                            //     $data = array();
                            //     if ($count == 13) {
                            //         $count = 1;
                            //     }
                            //     $getcolumn = Helper::getMonthColumn($count);
                            //     $setquery .= $getcolumn['lot_status'] . " = 'P', ";
                            //     $count++;
                            // }
                            // $setquery = rtrim($setquery, ", ");

                            // $getModelFunc = new getModelFunc();
                            // $schemaname = $getModelFunc->getSchemaDetails();

                            // $schemaname = 'trx_mgmt_cur_fy';   // For fin year change

                            // new for previous year arrear payment Date - 26-03-2023
                            $schemaname = DB::connection('pgsql_payment')->table('master_mgmt.m_financial_master')->where('financial_year', $finYear)->value('db_schema_name');

                            DB::beginTransaction();
                            $updateLotMaster = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $finYear)->where('lot_status', '8')->update([
                                'lot_status' => '1',
                                'pushed_at' => date("Y-m-d H:i:s")
                            ]);
                            // $queryupdate = "Update " . $schemaname . ".ben_payment_details bp set " . $setquery . "  from " . $schemaname . ".lot_details tld
                            // where tld.ben_id=bp.ben_id and tld.lot_no=" . $lotNo;
                            $queryupdate = "SELECT payment.push_generate_lot(" . $lotNo . ", " . $lotMonth . ", '" . $lotYear . "', '" . $lotNumber . "')";

                            $is_updated = DB::connection('pgsql_payment')->select($queryupdate);
                            $logmessage .= "Database updated successfully" . "\n";
                            DB::commit();
                            $response = array(
                                'status' => 1,
                                'msg' => 'Lot No:- ' . $lotNo . ' has been pushed to Bandhan Bank server.',
                                'type' => 'green',
                                'icon' => 'fa fa-check',
                                'title' => 'Success'
                            );
                        } else {
                            //  dd('ok2');
                            $updateLotStatus = DB::connection('pgsql_payment')->table('bandhan.lot_master')
                                ->where('lot_no', $lotNo)
                                ->where('lot_year', $finYear)
                                ->where('lot_status', '8')
                                ->update([
                                    'lot_status' => '0',
                                    'is_rejected' => 1
                                ]);
                            $logmessage .= "Error Message:- " . $errorMessage . "\n";
                            $response = array(
                                'status' => 3,
                                'msg' => $errorMessage . ' for lot no:- ' . $lotNo,
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Error'
                            );
                        }
                    } else if ($responseStatus === 'SUCCESS') {
                        // dd($responseStatus);
                        $logmessage .= "Succesfully Response Received:- " . "\n";
                        for ($i = $lotmonth; $i < $loopcount; $i++) {
                            $data = array();
                            if ($count == 13) {
                                $count = 1;
                            }
                            $getcolumn = Helper::getMonthColumn($count);
                            $setquery .= $getcolumn['lot_status'] . " = 'P', ";
                            $count++;
                        }
                        $setquery = rtrim($setquery, ", ");

                        // $getModelFunc = new getModelFunc();
                        // $schemaname = $getModelFunc->getSchemaDetails();

                        // $schemaname = 'trx_mgmt_cur_fy';   // For fin year change

                        // new for previous year arrear payment Date - 26-03-2023
                        $schemaname = DB::connection('pgsql_payment')->table('master_mgmt.m_financial_master')->where('financial_year', $finYear)->value('db_schema_name');
                        // dd($schemaname);

                        DB::beginTransaction();
                        $updateLotMaster = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $finYear)->where('lot_status', '8')->update([
                            'lot_status' => '1',
                            'pushed_at' => date("Y-m-d H:i:s")
                        ]);
                        // dd($updateLotMaster);
                        // $queryupdate = "Update " . $schemaname . ".ben_payment_details bp set " . $setquery . "  from " . $schemaname . ".lot_details tld
                        // where tld.ben_id=bp.ben_id and tld.lot_no=" . $lotNo;
                        $queryupdate = "SELECT payment.push_generate_lot(" . $lotNo . ", " . $lotMonth . ", '" . $lotYear . "', '" . $lotNumber . "')";
                        $is_updated = DB::connection('pgsql_payment')->select($queryupdate);
                        $logmessage .= "Database updated successfully" . "\n";
                        DB::commit();
                        $response = array(
                            'status' => 1,
                            'msg' => 'Lot No:- ' . $lotNo . ' has been pushed to Bandhan Bank server.',
                            'type' => 'green',
                            'icon' => 'fa fa-check',
                            'title' => 'Success'
                        );
                        // dd($is_updated, $logmessage, $response);
                    }
                    // dd($response);
                }
            }
            Storage::append($fileLocation, $logmessage);
            Storage::put($fileLocation);
        } catch (\Exception $e) {
            //dd($e);
            $updateLotStatus = DB::connection('pgsql_payment')->table('bandhan.lot_master')
                ->where('lot_no', $lotNo)
                ->where('lot_year', $finYear)
                ->where('lot_status', '8')
                ->update([
                    'lot_status' => '0'
                ]);
            DB::Rollback();
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' => 'Oops. Something wrong for lot no:- ' . $lotNo . '. Please try agian later.',
            );
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }


    public function LBGetTransactionLotInfo(Request $request)
    {
        // dd($request->all());
        $statusCode = 200;
        $response = [];
        try {

            $lotNo = base64_decode($request->lot_no);
            // dd($lotNo);
            $finYear = $request->year;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails($finYear);
            // dump($finYear);
            // $schemaname = 'trx_mgmt_cur_fy';   // For fin year change

            // new for previous year arrear payment Date - 26-03-2023
            $schemaname = DB::connection('pgsql_master')->table('master_mgmt.m_financial_master')->where('financial_year', $finYear)->value('db_schema_name');
            // dump($schemaname);
            $lot_info = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $finYear)->first();
            // dd($lot_info);
            $TriggeredByUserId = config('bandhan.TriggeredByUserId');
            $ApplicationId = config('bandhan.ApplicationId');
            $LotTransactionInfoActionId = config('bandhan.LotTransactionInfoActionId');

            $apiFieldArray = ['@year', '@month', '@lotNumber'];
            $lotnumber = $lot_info->file_name;
            $lotYear = $lot_info->lot_year;
            $lotMonth = $lot_info->lot_month;
            $explodeYear = explode("-", $lotYear);
            // $year = $explodeYear[0];
            if ($lotMonth <= 3) {
                $year = $explodeYear[1];
            } else {
                $year = $explodeYear[0];
            }
            // dd($TriggeredByUserId, $ApplicationId, $LotTransactionInfoActionId);
            $apiFieldValueArray = array('0' => $year, '1' => $lotMonth, '2' => $lotnumber);
            $apiResult = BandhanPayment::curlPayment($TriggeredByUserId, $ApplicationId, $LotTransactionInfoActionId, $apiFieldArray, $apiFieldValueArray);
            // dd($apiResult);
            if ($apiResult['errorCurl']) {
                $response = array(
                    'status' => 4,
                    'msg' => $apiResult['errorCurl'] . ' for lot no:- ' . $lotNo,
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Error'
                );
            } else {
                $decodeResponse = json_decode(json_decode($apiResult['result'], TRUE));
                // print_r(json_decode($apiResult['result']));
                // die;
                //  Completed
                $insertTbl = [
                    'lot_no' => $lotNo,
                    'api_triggered_by_user_id' => $TriggeredByUserId,
                    'api_application_id' => $ApplicationId,
                    'api_lot_validation_info_action_id' => $LotTransactionInfoActionId,
                    'api_response' => $apiResult['result'],
                    'lot_type' => 'Payment-DDO',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                DB::connection('pgsql_payment')->table('master_mgmt.bandhan_bank_api_response')->insert($insertTbl);
                if ($decodeResponse->ResponseStatus == 'SUCCESS') {
                    if (!empty($decodeResponse->DbTupleLite)) {
                        if (!empty($decodeResponse->DbTupleLite[0]->RecordList)) {
                            if (count($decodeResponse->DbTupleLite[0]->RecordList[0]->Record) > 0) {
                                $finalRecord = $decodeResponse->DbTupleLite[0]->RecordList[0]->Record;
                                foreach ($finalRecord as $recordResult) {
                                    if ($recordResult->Fn === 'status') {
                                        if ($recordResult->Fv === "Completed") {
                                            $update = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $finYear)->where('lot_status', '1')->update([
                                                'lot_status' => '2'
                                            ]);
                                        } else {
                                            $update = 0;
                                        }
                                    }
                                }
                                if ($update == 1) {
                                    $response = array(
                                        'status' => 1,
                                        'msg' => 'Lot No:- ' . $lotNo . ' is complete. Please import the result.',
                                        'type' => 'green',
                                        'icon' => 'fa fa-check',
                                        'title' => 'Success'
                                    );
                                } else {
                                    $response = array(
                                        'status' => 2,
                                        'msg' => 'Lot No:- ' . $lotNo . '  is incomplete. Please try later.',
                                        'type' => 'blue',
                                        'icon' => 'fa fa-exclamation',
                                        'title' => 'Incomplete'
                                    );
                                }
                            } else {
                                $response = array(
                                    'status' => 3,
                                    'msg' => 'Lot No:- ' . $lotNo . '  is incomplete. Please try later.',
                                    'type' => 'blue',
                                    'icon' => 'fa fa-exclamation',
                                    'title' => 'Incomplete'
                                );
                            }
                        } else {
                            $response = array(
                                'status' => 4,
                                'msg' => 'Lot No:- ' . $lotNo . '  is incomplete. Please try later.',
                                'type' => 'blue',
                                'icon' => 'fa fa-exclamation',
                                'title' => 'Incomplete'
                                );
                        }
                    } else {
                        $response = array(
                            'status' => 5,
                            'msg' => 'Lot No:- ' . $lotNo . ' is incomplete. Please try later.',
                            'type' => 'blue',
                            'icon' => 'fa fa-exclamation',
                            'title' => 'Incomplete'
                        );
                    }
                } else {
                    $response = array(
                        'status' => 6,
                        'msg' => 'Lot No:- ' . $lotNo . ' is incomplete. Please try later.',
                        'type' => 'blue',
                        'icon' => 'fa fa-exclamation',
                        'title' => 'Incomplete'
                    );
                }
            }
        } catch (\Exception $e) {

            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' => 'Oops. Something wrong for lot no:- '. $lotNo.'. Please try agian later.',
            );
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }



    public function LBGetTransactionLotStatusDetails(Request $request)
    {
        // dd($request->all());
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in ajax call.');
            return response()->json($response, $statusCode);
        }

        $lotNo = base64_decode($request->lot_no);
        $finYear = $request->year;
        // dump($lotNo);
        // dd($finYear);
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails($finYear);

        // $schemaname = 'trx_mgmt_cur_fy';   // For fin year change

        // new for previous year arrear payment Date - 26-03-2023
        $schemaname = DB::connection('pgsql_master')->table('master_mgmt.m_financial_master')->where('financial_year', $finYear)->value('db_schema_name');

        $lotInfo = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $finYear)->first();
        // dd($lotInfo);
        $distCode = $lotInfo->dist_code;
        $logmessage = "";
        $logmessage .= date('l d-M-Y h:i:s A') . "\n";
        $logmessage .= "------------------------------" . "\n";
        $logFileName = $lotInfo->file_name;
        $fileLocation = 'bandhanlog/' . $logFileName . '_BenTranResponse' . '.txt';

        $TriggeredByUserId = config('bandhan.TriggeredByUserId');
        $ApplicationId = config('bandhan.ApplicationId');
        $LotTransactionDetActionId = config('bandhan.LotTransactionDetActionId');

        $apiFieldArray = ['@lotNumber'];
        $lotNumber = $lotInfo->file_name;
        $apiFieldValueArray = array('0' => $lotNumber);
        // dd($TriggeredByUserId, $ApplicationId, $LotTransactionDetActionId, $apiFieldArray, $apiFieldValueArray);
        $apiResult = BandhanPayment::curlPayment($TriggeredByUserId, $ApplicationId, $LotTransactionDetActionId, $apiFieldArray, $apiFieldValueArray);
        // dd($apiResult);
        $logmessage .= "Fetching curl successfully" . "\n";
        if ($apiResult['errorCurl']) {
            $logmessage .= "Curl Error:- " . $apiResult['errorCurl'] . "\n";
            $response = array(
                'status' => 4,
                'msg' => $apiResult['errorCurl'],
                'type' => 'red',
                'icon' => 'fa fa-warning',
                'title' => 'Error'
            );
        } else {
            $decodeResponse = json_decode(json_decode($apiResult['result'], TRUE));
            // print_r($decodeResponse);
            // die;
            try {
                $insertTbl = [
                    'lot_no' => $lotNo,
                    'api_triggered_by_user_id' => $TriggeredByUserId,
                    'api_application_id' => $ApplicationId,
                    'api_lot_validation_info_action_id' => $LotTransactionDetActionId,
                    'api_response' => $apiResult['result'],
                    'lot_type' => 'PaymentImportResponse-DDO',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                DB::connection('pgsql_payment')->table('master_mgmt.bandhan_bank_api_response')->insert($insertTbl);
                if (!empty($decodeResponse->DbTupleLite[0])) {
                    $encryptedResponse = $decodeResponse->DbTupleLite[0]->RecordList[0]->Record[0]->Fv;
                    $key = Config::get('bandhan.EncryptionKey');
                    $iv = Config::get('bandhan.IvData');
                    $decryptData = BandhanPayment::decryptCode($key, $encryptedResponse);
                    $decompressed = gzuncompress(base64_decode($decryptData));
                    $valueDecompressed = "'" . $decompressed . "'";
                    $filename = $lotInfo->file_name . '_TranResp' . '.txt';
                    $savefile = Storage::disk('bandhanTransactionResponse')->put($filename, $decompressed);
                    $logmessage .= "File stored successfully" . "\n";
                    // dd($valueDecompressed);
                    $query = "select " . $schemaname . ".update_lot_payment_status(" . $distCode . "," . $lotNo . ", " . $valueDecompressed . ")";
                    // dd($query);
                    $func_call = DB::connection('pgsql_payment')->select($query);
                    // $func_call = DB::select('call '.$schemaname.'.response_validation_lot(?, ?)', [ $lotNo, $decompressed ]);
                    $logmessage .= "DB function called successfully" . "\n";
                    if ($func_call) {
                        $response = array(
                            'status' => 1,
                            'msg' => 'Lot No:- ' . $lotNo . '  imported response successfully.',
                            'type' => 'green',
                            'icon' => 'fa fa-check',
                            'title' => 'Success'
                        );
                    } else {
                        $response = array(
                            'status' => 6,
                            'msg' => 'Oops. Something wrong for lot no:- ' . $lotNo . '.. Please try again later.',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Error!!'
                        );
                    }
                    Storage::append($fileLocation, $logmessage);
                    Storage::put($fileLocation);
                } else {
                    DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('dist_code', $distCode)->update(['is_rejected' => 5]);
                    return $response = array(
                        'status' => 1,
                        'msg' => 'Blank DbTupleLite for Lot No:- ' . $lotNo . '. Please try after sometime',
                        'type' => 'warning',
                        'icon' => 'fa fa-info',
                        'title' => 'Error'
                    );
                }
            } catch (\Exception $e) {
                // dd($e);
                $response = array(
                    'exception' => true,
                    'exception_message' => $e->getMessage(),
                    // 'exception_message' => 'Oops. Something wrong for lot no:- ' . $lotNo . '... Please try agian later.',
                );
                $statusCode = 400;
            } finally {
                return response()->json($response, $statusCode);
            }
        }
    }


    public function LotCompileDb(Request $request)
    {
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in ajax call.');
            return response()->json($response, $statusCode);
        }

        $lotNo = base64_decode($request->lot_no);
        $finYear = $request->year;
        $getModelFunc = new getModelFunc();
        // $schemaname = $getModelFunc->getSchemaDetails($finYear);

        // $schemaname = 'trx_mgmt_cur_fy';   // For fin year change

        // new for previous year arrear payment Date - 26-03-2023
        $schemaname = DB::connection('pgsql_master')->table('master_mgmt.m_financial_master')->where('financial_year', $finYear)->value('db_schema_name');

        $lotInfo = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $finYear)->first();
        $distCode = $lotInfo->dist_code;


        try {
            $func_call = DB::connection('pgsql_payment')->select("select " . $schemaname . ".update_ben_payment_status(" . $lotNo . ")");
            if ($func_call) {
                $response = array(
                    'status' => 1,
                    'msg' => 'Lot No:- ' . $lotNo . '  compiled successfully.',
                    'type' => 'green',
                    'icon' => 'fa fa-check',
                    'title' => 'Success'
                );
            } else {
                $response = array(
                    'status' => 6,
                    'msg' => 'Oops. Something wrong for lot no:- ' . $lotNo . '. Please try again later.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Error!!'
                );
            }
        } catch (\Exception $e) {
            $response = array(
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' => 'Oops. Something wrong for lot no:- ' . $lotNo . '. Please try agian later.',
            );
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }

    public function LotCancelDb(Request $request)
    {
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in ajax call.');
            return response()->json($response, $statusCode);
        }

        $lotNo = base64_decode($request->lot_no);
        $lotYear = $request->year;
        $designation_id = Auth::user()->designation_id;
        $created_by = Auth::user()->id;
        $ip_address = $request->ip();

        $getModelFunc = new getModelFunc();
        // $schemaname = $getModelFunc->getSchemaDetails($finYear);

        // $schemaname = 'trx_mgmt_cur_fy';   // For fin year change

        // new for previous year arrear payment Date - 26-03-2023
        $schemaname = DB::connection('pgsql_master')->table('master_mgmt.m_financial_master')->where('financial_year', $lotYear)->value('db_schema_name');

        $lotInfo = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $lotNo)->where('lot_year', $lotYear)->first();
        $distCode = $lotInfo->dist_code;
        $lotFileName = $lotInfo->file_name;
        $lotMonth = $lotInfo->lot_month;

        $insertLog = [];
        $insertLog['created_by'] = $created_by;
        $insertLog['created_by_dist_code'] = $distCode;
        $insertLog['created_at'] = date('Y-m-d H:i:s');
        $insertLog['op_type'] = 'DefuncLot';
        $insertLog['ip_address'] = $ip_address;
        $insertLog['designation_id'] = $designation_id;
        $insertLog['lot_no'] = $lotNo;
        $insertLog['lot_year'] = $lotYear;


        DB::connection('pgsql_payment')->beginTransaction();
        try {
            $func_call = DB::connection('pgsql_payment')->select("select " . $schemaname . ".bandhan_payment_lot_cancel(" . $lotNo . ", " . $distCode . ", " . $lotMonth . ", '" . $lotYear . "', '" . $lotFileName . "')");
            $benAcceptRejectInfo = DB::connection('pgsql_payment')->table('public.ben_accept_reject_info')->insert($insertLog);
            if ($func_call && $benAcceptRejectInfo) {
                DB::connection('pgsql_payment')->commit();
                $response = array(
                    'status' => 1,
                    'msg' => 'Lot No:- ' . $lotNo . '  defunctioned successfully.',
                    'type' => 'green',
                    'icon' => 'fa fa-check',
                    'title' => 'Success'
                );
            } else {
                DB::connection('pgsql_payment')->rollBack();
                $response = array(
                    'status' => 6,
                    'msg' => 'Oops. Something wrong for lot no:- ' . $lotNo . '. Please try again later.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Error!!'
                );
            }
        } catch (\Exception $e) {
            DB::connection('pgsql_payment')->rollBack();
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' => 'Oops. Something wrong for lot no:- ' . $lotNo . '. Please try agian later.',
            );
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }

    public function defuncLotInBulkIndex()
    {
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $finYear = Config::get('constants.fin_year');
        $monthVal = Config::get('constants.monthval');
        $distObj = District::select('district_code', 'district_name')->get();

        if ($designation_id == 'Admin') {
            return view('generic-lot.defunc-lot-in-bulk', [
                'finYear' => $finYear,
                'monthVal' => $monthVal,
                'distObj' => $distObj,
            ]);
        } else {
            return redirect('/')->with('success', 'Unauthorized');
        }
    }

    public function defuncLotInBulkPost(Request $request)
    {
        // dd('OK');
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in ajax call.');
            return response()->json($response, $statusCode);
        }
        try {
            $finYear = $request->lot_year;
            $lotType = $request->lot_type;
            $district = $request->district;
            $lotMonth = $request->lot_month;
            $counter = 0;

            $schemaname = DB::connection('pgsql_master')->table('master_mgmt.m_financial_master')->where('financial_year', $finYear)->value('db_schema_name');

            $query = DB::connection('pgsql_payment')->table('bandhan.lot_master')->select('lot_no', 'file_name', 'lot_month', 'dist_code')->where('is_adjustable', 0)->where('lot_year', $finYear)->where('lot_type', $lotType)->where('dist_code', $district)->where('lot_status', '0');
            if (!empty($lotMonth)) {
                $query->where('lot_month', $lotMonth);
            }
            // $lotQuery = $query->tosql();
            // dd($lotQuery, $query->getBindings());
            $lotInfo = $query->get();
            // dd($schemaname);
            foreach ($lotInfo as $lotNo) {
                $counter++;
                $func_call = DB::connection('pgsql_payment')->select("select " . $schemaname . ".bandhan_payment_lot_cancel(" . $lotNo->lot_no . ", " . $lotNo->dist_code . ", " . $lotNo->lot_month . ", '" . $finYear . "', '" . $lotNo->file_name . "')");
            }
            // dd($counter);
            if ($func_call[0]->bandhan_payment_lot_cancel == 1) {
                $response = array(
                    'status' => 1,
                    'msg' => $counter . ' numbers of lot defunctioned successfully.',
                    'type' => 'green',
                    'icon' => 'fa fa-check',
                    'title' => 'Success'
                );
            } else {
                $response = array(
                    'status' => 6,
                    'msg' => 'Oops. Something wrong. Please try again later.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Error!!'
                );
            }
        } catch (\Exception $e) {
            dd($e);
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' => 'Oops. Something wrong. Please try again later..',
            );
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
}
