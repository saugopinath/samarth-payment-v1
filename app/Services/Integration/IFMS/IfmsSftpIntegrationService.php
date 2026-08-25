<?php

namespace App\Services\Integration\IFMS;

use App\Models\PaymentLotMaster;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Models\IfmsTransactionLotDetail;
use App\Models\IfmsPaymentLotMasterAdditionalInfo;
use App\Models\PaymentMainSetting;
use App\Models\Scheme;
use App\Services\Contracts\PaymentSBIIntegrationInterface;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IfmsSftpIntegrationService implements PaymentSBIIntegrationInterface
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new IfmsSftpIntegrationService();
        }
        return self::$instance;
    }

    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        // TODO: Implement IFMS SFTP payload generation
        return true;
    }

    public function pushToTarget(PaymentLotMaster $lotMaster)
    {
        set_time_limit(0);
        try {
            // dd('ok');
            $DRN_part = $lotMaster->lot_no;
            $scheme_id = $lotMaster->scheme_id;
            
            $financialYear = $lotMaster->lot_year;
            $lotMonth = $lotMaster->lot_month;

            $setting = PaymentMainSetting::where('scheme_id', $scheme_id)
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

            $schemeDetail = Scheme::find($scheme_id);
            $schemeName = $schemeDetail ? ($schemeDetail->display_name ?? $schemeDetail->name) : '';

            $mytime = Carbon::now();
            $dateDate = $mytime->format('d');
            $dateMonth = $mytime->format('m');
            $dateYear = $mytime->format('Y');

            $serialNo = '01'; // Can be made dynamic if needed

            $filename = $ddocode . $partyCode . $dateDate . $dateMonth . $dateYear . $serialNo . $DRN_part;
            $DRN_full = $dateYear . $dateMonth . $partyCode . $DRN_part;

            $file_name = $lotMaster->file_name;
          
            $ben_data = IfmsTransactionLotDetail::where('lot_no', $DRN_part)
                    ->where('scheme_id', $scheme_id)
                    ->get();
                    
                $totalvalue = $ben_data->sum('amount_rs');
                $benf_count = $ben_data->count();
                
                $xmlFile = new DOMDocument("1.0", 'UTF-8');
                $xmlFile->formatOutput = true;

                $bulkecs  = $xmlFile->createElement("bulkecs");
                $xmlFile->appendChild($bulkecs);
                $drn  = $xmlFile->createElement("DRN", $DRN_full);
                $bulkecs->appendChild($drn);

                $bulkecs->setAttribute('totalamount', $totalvalue);
                $bulkecs->setAttribute('benfcount', $benf_count);

                foreach ($ben_data as $details) {
                    $beneficiary = $xmlFile->createElement("BENEFICIARY");
                    $bulkecs->appendChild($beneficiary);
                    $name = $xmlFile->createElement("BENF_NAME", substr(trim($details->ben_name ?? ''), 0, 99));
                    $beneficiary->appendChild($name);
                    $acc_no = $xmlFile->createElement("ACCOUNT_NO", trim($details->accno ?? ''));
                    $beneficiary->appendChild($acc_no);
                    $ifsc = $xmlFile->createElement("IFSC_CODE", trim($details->ifsc ?? ''));
                    $beneficiary->appendChild($ifsc);
                    $mobile_no = $xmlFile->createElement("MOBILE_NO", trim($details->mobile_no ?? ''));
                    $beneficiary->appendChild($mobile_no);
                    $amount = $xmlFile->createElement("AMOUNT", trim($details->amount_rs ?? ''));
                    $beneficiary->appendChild($amount);
                    $ben_id = $xmlFile->createElement("ID", trim($details->ben_id ?? ''));
                    $beneficiary->appendChild($ben_id);
                    $order_no_date = $xmlFile->createElement("ORDER_NO", trim($details->order_no_date ?? ''));
                    $beneficiary->appendChild($order_no_date);
                    $unique_id = $xmlFile->createElement("UNIQUE_ID", trim($details->unique_id ?? ''));
                    $beneficiary->appendChild($unique_id);
                    $remarks = $xmlFile->createElement("REMARKS", trim($schemeName));
                    $beneficiary->appendChild($remarks);
                }

                $create_xml_file_path = storage_path("app/ifms_xml/xml_file/pushed/". $partyCode ."/" . $filename . ".xml");
                if (!file_exists(dirname($create_xml_file_path))) {
                    mkdir(dirname($create_xml_file_path), 0775, true);
                    Storage::put('ifms_xml/pushed/' . $partyCode . '/' . $filename . '.xml', $xml_File);

                }
               
                
                $xmlFile->save($create_xml_file_path);
                $xml_File = $xmlFile->saveXML($xmlFile->documentElement);
                
                if (app()->environment('local')) {
                        try {
                            $doneFileName = $filename . '.xml.done';
                            Storage::disk('ifms_sftp_' . $partyCode)->put(config('ifms.paths.epayment_files_002') . '/' . $doneFileName, '');
                            Storage::disk('ifms_sftp_' . $partyCode)->put(config('ifms.paths.epayment_files_002') . '/ACK/' . $filename . '.xml', $xml_File);
                           }
                         catch (\Exception $e) {
                            DB::rollback();
                           //dd($e);
                           }
                            $resXml = new DOMDocument("1.0", 'UTF-8');
                            $resXml->xmlStandalone = true;
                            $resXml->formatOutput = true;
                            
                            $paymentDetail = $resXml->createElement("paymentDetail");
                            $resXml->appendChild($paymentDetail);
                            
                            $paymentDetail->appendChild($resXml->createElement("DRN", $DRN_full));
                            $paymentDetail->appendChild($resXml->createElement("voucherNo", "223573"));
                            $paymentDetail->appendChild($resXml->createElement("voucherDate", "11/07/2026"));
                            $paymentDetail->appendChild($resXml->createElement("tokenNo", "15584"));
                            $paymentDetail->appendChild($resXml->createElement("tokenDate", "09/07/2026"));
                            
                            foreach ($ben_data as $details) {
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
                                $benfDetail->appendChild($resXml->createElement("utrNo", ""));
                            }
                            
                            $resXmlString = $resXml->saveXML();
                            $responseFileName = $partyCode . ($file_name ?: $filename . '.xml');
                            if (substr($responseFileName, -4) !== '.xml') {
                                $responseFileName .= '.xml';
                            }
                            Storage::disk('ifms_sftp_' . $partyCode)->put(config('ifms.paths.epayment_files_005') . '/' . $responseFileName, $resXmlString);
                }
               
                 Storage::disk('ifms_sftp_' . $partyCode)->put(config('ifms.paths.epayment_files_006') . '/' . $filename . '.xml', $xml_File);   

                // $ifmsDisk->put(config('ifms.paths.epayment_files_006') . '/' . $filename . '.xml', $xml_File); // uncomment in production
                
                $exists = true; // simulated or could be: $ifmsDisk->exists(config('ifms.paths.epayment_files_006') . '/' . $filename . '.xml');
                
                if ($exists) {

                    DB::beginTransaction();
                    try {
                        $lotMaster->file_name = $filename.'.xml';

                        $lotMaster->cur_status = config('payment_lot.status.common.push');
                        $lotMaster->payment_push_date = now();
                        $lotMaster->save();

                        IfmsPaymentLotMasterAdditionalInfo::updateOrCreate(
                            [
                                'lot_no' => $lotMaster->lot_no,
                                'scheme_id' => $lotMaster->scheme_id,
                                'lot_year' => $lotMaster->lot_year,
                            ]
                        );

                        DB::commit();
                        
                        return true;
                    } catch (\Exception $e) {
                        DB::rollback();
                        return false;
                    }
                } else {
                   return false;
                }
            
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkdotDone(PaymentLotMaster $lotMaster)
    {
        set_time_limit(0);
        try { 
            $scheme_id = $lotMaster->scheme_id;          
            $schemeDetail =  DB::table('m_scheme')->select('party_code')->where('id',$scheme_id)->first();
            $partyCode = $schemeDetail->party_code;
            $lot_no = $lotMaster->lot_no;  
            $file_name = $lotMaster->file_name;      		     
            //$exists = Storage::disk('ifms_sftp_' . $partyCode)->exists(config('ifms.paths.epayment_files_002') . '/ACK/' . $file_name . '.xml');

            $exists = Storage::disk('sftp_' . $partyCode)->exists(config('ifms.paths.epayment_files_002') . $file_name . '.xml.done');
            if ($exists) {
              
                $lotMaster->cur_status = config('payment_lot.status.ifms.dotdone'); // GENERATED,PUSHED AND RESPONSE RECEIVED
                $lotMaster->response_receive_date = now();
                $lotMaster->save();
                   
               
            } else {

               $response = array(
					'status' => 3, 'msg' => 'Lot no. ' . $lot_no . ' has not yet been received by IFMS.',
					'type' => 'orange', 'icon' => 'fa fa-info', 'title' => 'Not Received'
				);
                
            }
        } catch (\Exception $e) {
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' => 'Oops. Connection time out. Please try agian later.',
            );
        }

        return $response ?? [];
    }
    public function checkAcknowledge(PaymentLotMaster $lotMaster)
    {
       	$statusCode = 200;
		$response = [];
		try {
			$scheme_id = $lotMaster->scheme_id;          
            $schemeDetail =  DB::table('m_scheme')->select('party_code')->where('id',$scheme_id)->first();
            $partyCode = $schemeDetail->party_code;
            $lot_no = $lotMaster->lot_no;  
            $file_name = $lotMaster->file_name;     
			$exists = Storage::disk('sftp_' . $partyCode)->exists(config('ifms.paths.epayment_files_002') . '/ACK/' . $file_name . '.xml');

			if ($exists) {
				if (config('payment_lot.status.common.ack')==$lotMaster->cur_status) {
                    $response = array(
						'status' => 3, 'msg' => 'Bill reference no is already generated.',
						'type' => 'orange', 'icon' => 'fa fa-info', 'title' => 'Complete'
					);
                }
                else{
                    $remote_file = Storage::disk('sftp_' . $partyCode)->get(config('ifms.paths.epayment_files_002') . '/ACK/' . $file_name . '.xml');
					Storage::put('ifms_xml/ack/' . $partyCode . '/ACK' . $file_name . '.xml', $remote_file);
					$remote_xml_file = simplexml_load_string($remote_file);

					$IFMS_REF_NO = $remote_xml_file->IFMS_REF_NO;


					$lotMaster->cur_status = config('payment_lot.status.common.ack'); // GENERATED,PUSHED AND RESPONSE RECEIVED
                    $lotMaster->save();

					$wrong_file = $this->wrong_file_status($partyCode,$filename);

					if ($wrong_file) {
						$response = array(
							'status' => 1, 'msg' => 'Reference No. ' . $IFMS_REF_NO . ' generated for Lot no. ' . $lot_no . '. ' . $wrong_file,
							'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success'
						);
					} else {
						$response = array(
							'status' => 2, 'msg' => 'Unable to check wrongdata.',
							'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
						);
					}
				} 
			} else {

				$list = Storage::disk('sftp_' . $partyCode)->files(config('ifms.paths.epayment_files_003'));
				$matches = preg_grep('/^' . config('ifms.paths.epayment_files_003') . '\/' . $file_name . '/', $list);
				$matchescount = count($matches);
				if ($matchescount == 0) {

					$filename = '';
				} else {
					foreach ($matches as $matchitem) {
						$matchescount = $matchescount - 1;
						if ($matchescount == 0) {

							$filename = $matchitem;
						}
					}
				}
				$matchexists = Storage::disk('sftp_' . $partyCode)->exists($filename);
				if ($matchexists) {
					$remote_error_file = Storage::disk('sftp_' . $partyCode)->get($filename);
					if (!empty($remote_error_file)) {
						$count_beneficiary = 0;
						$remote_xml_file_data = simplexml_load_string($remote_error_file);
						if (strpos($filename, 'D_') == true) {
							foreach ($remote_xml_file_data as $key => $value) {
								$count_beneficiary++;
							}
						}
						$beneficiary_count_from_db = PaymentLotDetail::where('lot_no', $lot_no)
							->where('scheme_id', $scheme_id)->count();
						if ($beneficiary_count_from_db == $count_beneficiary) {
							$wrong_file = $this->wrong_file_status($partyCode,$filename);
							if ($wrong_file) {
								$response = array(
									'status' => 4, 'msg' => 'Reference No. not generated for Lot no. ' . $lot_no . ' as all beneficiaries failed at IFMS. ' . $wrong_file,
									'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
								);
							} else {
								$response = array(
									'status' => 5, 'msg' => 'Unable to check wrong data.',
									'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
								);
							}
						} else {
							$response = array(
								'status' => 6, 'msg' => 'IFMS Reference Not Generated.',
								'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
							);
						}
					} else {
						$response = array(
							'status' => 7, 'msg' => 'System Error.',
							'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
						);
					}
				} else {
					$response = array(
						'status' => 8, 'msg' => 'No Acknowledgement, No Error File.',
						'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
					);
				}
			}
		} catch (\Exception $e) {
			$response = array(
				'exception' => true,
				'exception_message' => $e->getMessage(),
				// 'exception_message' => 'Oops. Connection time out. Please try agian later.',
			);
    }
        return $response ?? [];
    }
  
    
     public function checkResponse(PaymentLotMaster $lotMaster)
    {
         $scheme_id = $lotMaster->scheme_id;          
         $schemeDetail =  DB::table('m_scheme')->select('party_code')->where('id',$scheme_id)->first();
         $partyCode = $schemeDetail->party_code;
         $lot_no = $lotMaster->lot_no;  
         $file_name = $lotMaster->file_name;      
         $exists = Storage::disk('sftp_' . $partyCode)->exists($filename);
		 $return_status = '';
		 $exists = Storage::disk('sftp_' . $partyCode)->exists(config('ifms.paths.ePayment_Files_005') . $file_name . '.xml');
         if ($exists) {
            $codemasterChilds = collect(config('codemaster.childs'));
            $failedTypeCode = $codemasterChilds->where('short_name', 'payment_failed')->first()['code'] ?? '1415';
            $sbiSourceCode = $codemasterChilds->where('short_name', 'ifms')->first()['code'] ?? '5202';
            $return_status = 'success';		
            $remote_file = Storage::disk('sftp_' . $partyCode)->get(config('ifms.paths.ePayment_Files_005') . $file_name . '.xml');
			$fname = substr($filename, 18);
			Storage::put('ifms_xml/rbi_resp/' . $partyCode . $fname, $remote_file);
			$remote_xml_file = simplexml_load_string($remote_file);	
            $DRN = $remote_xml_file->DRN;
            $voucherNo = $remote_xml_file->voucherNo;
            $voucherDate = $remote_xml_file->voucherDate;
            $tokenNo = $remote_xml_file->tokenNo;
            $tokenDate = $remote_xml_file->tokenDate;
            $success_count = 0;
            $failed_count = 0;
            $i = 0;
           	foreach ($remote_xml_file as $key => $value) {

			$j = 0;
			foreach ($value as $key2[$j] => $val2[$j]) {
				if ($j == 0) {
					$acc_no = $val2[$j];
				}
				if ($j == 1) {
					$amount = $val2[$j];
				}
				if ($j == 2) {
					$fileName = $val2[$j];
				}
				if ($j == 3) {
					$ifscCode = $val2[$j];
				}
				if ($j == 4) {
					$paymentDt = $val2[$j];
				}
				if ($j == 5) {
					$reason = $val2[$j];
				}
				if ($j == 6) {
					$refBenfId = $val2[$j];
				}
				if ($j == 7) {
					$refOrdernoDt = $val2[$j];
				}
				if ($j == 8) {
					$referenceNo = $val2[$j];
				}
				if ($j == 9) {
					$status = $val2[$j];
				}
				if ($j == 10) {
					$utrNo = $val2[$j];
					if ($status == 'Success') {
						$success_count = $success_count + 1;
					} elseif ($status == 'Failed') {
						$failed_count = $failed_count + 1;
                        \App\Models\FailedPaymentDetail::Create([
                                        'lot_no' => $lotMaster->lot_no,
                                        'ben_id' => $refBenfId,
                                        'scheme_id' => $lotMaster->scheme_id,
                                        'status_code' => $mapped_status_code,
                                        'remarks' => $reason,
                                        'failed_type' => $failedTypeCode,
                                        'failed_source' => $sbiSourceCode
                                    ]);
					} else {
						break;						
					}
				}
                    
                            // Update IfmsTransactionLotDetail
                            $detail = IfmsTransactionLotDetail::where('lot_no', $lotMaster->lot_no)
                                ->where('scheme_id', $lotMaster->scheme_id)->where('ben_id', $refBenfId)->first();

                            if ($detail) {
                                $codemasterStatus = \App\Models\Codemaster::where('short_name', $status)
                                    ->where('parent_short_code', 'ifms_status_code')
                                    ->first();
                                $mapped_status_code = $codemasterStatus ? $codemasterStatus->code : $credit_status;

                                $detail->status = $mapped_status_code; // Map to our internal codemaster code
                                $detail->utr_no = $utrNo;
                                $detail->voucher_no = $voucherNo;
                                $detail->voucher_date = $voucherDate;
                                $detail->token_no = $tokenNo;
                                $detail->token_date = $tokenDate;
                                $detail->rbi_failed_count = $failed_count;
                                 $detail->rbi_success_count = $success_count;
                                // Add other fields if required by your DB schema
                                $detail->save();

                                
                            }

				$j = $j + 1;
			}

			$i = $i + 1;
		}
		 }else{
			$return_status = 'error';
		 }
		return $return_status;
    }
   public function wrong_file_status($partyCode,$filename)
	{      /////parse scheme_id, lot_no

		$scheme_id = $request->get('scheme_id');          ////parsed value
		$schemeDetail =  DB::table('m_scheme')->select('party_code')->where('id', '=', $scheme_id)->first();
		$partyCode = $schemeDetail->party_code;

		$lot_no = $request->get('lot_no');     		     ////parsed value
		$existing_filename_details =  DB::connection('pgsql_paywrite')->table('payment.lot_master')->select('file_name')->where('lot_no', $lot_no)->where('scheme_id', $scheme_id)->first();
		$file_name = $existing_filename_details->file_name;
		$DRN_part = substr($file_name, 22);
		$list = Storage::disk('sftp_' . $partyCode)->files('ePayment_Files_003');
		//		print_r($list);
		$matchingFiles = preg_grep('/^ePayment_Files_003\/' . $file_name . '/', $list);
		/*=====changed block===*/
		$count = count($matchingFiles);
		if ($count == 0) {
			//echo $item;
			$filename = '';
		} else {
			foreach ($matchingFiles as $item) {
				$count = $count - 1;
				if ($count == 0) {
					//echo $item;
					$filename = $item;
				}
			}
		}
		/*=====changed block===*/
		//changed$filename = implode("",$matchingFiles);
		//echo $filename;
		$exists = Storage::disk('sftp_' . $partyCode)->exists($filename);
		$return_status = '';
		if ($exists) {
			//echo ' Wrong Data File Received ';

			$remote_file = Storage::disk('sftp_' . $partyCode)->get($filename);
			$fname = substr($filename, 18);
			Storage::put('ifms_xml/ifms_resp/' . $partyCode . $fname, $remote_file);
			$remote_xml_file = simplexml_load_string($remote_file);

			if (strpos($filename, 'D_') == true) {
				//echo 'Wrong Data';
				DB::connection('pgsql_paywrite')->table('payment.lot_master')->where('lot_no', $DRN_part)->where('scheme_id', $scheme_id)->update(['wrongdata_status' => 1]);
				DB::connection('pgsql_paywrite')->table('ifms.transaction_lot')->where('lot_no', $DRN_part)->where('scheme_id', $scheme_id)->update(['wrongdata_status' => 1]);
				DB::connection('pgsql_paywrite')->table('ifms.transaction_payload')->where('lot_no', $DRN_part)->where('scheme_id', $scheme_id)->where('file_name', $file_name)->update(['received_ifms_error_file' => $filename]); //xml(['received_ifms_error_payload' => $remote_file]);

				$i = 0;
				foreach ($remote_xml_file as $key => $value) {
					$j = 0;
					foreach ($value as $key2[$j] => $val2[$j]) {
						//echo $key2[$j].$i.$j.'=';
						//echo $val2[$j].' ';
						if ($j == 3) {
							$acc_no = $val2[$j];
						}
						if ($j == 4) {
							$ifsc = $val2[$j];
						}
						if ($j == 5) {
							$mob_no = $val2[$j];
						}
						if ($j == 7) {
							$id = $val2[$j];
						}
						if ($j == 10) {
							DB::connection('pgsql_paywrite')->table('ifms.ifms_return_details')->insert(['file_name' => $filename, 'drn_part' => $DRN_part, 'scheme_id' => $scheme_id, 'ben_id' => $id, 'error_reason' => $val2[$j]]);
							$pensionidArray = DB::connection('pgsql_paywrite')->table('ifms.transaction_lot_details')->select('pension_id')->where('ben_id', $id)/*->where('acc_no', $acc_no)->where('ifsc', $ifsc)->where('mobile_no', $mob_no)*/->where('drn_part', $DRN_part)->where('scheme_id', $scheme_id)->get();
							$pension_id = $pensionidArray[0]->pension_id;

							//echo 'pension_id: '.$pension_id; //echo ' error reason: '.$val2[$j].'</br>';
							// DB::table('pension.beneficiary')->where('id', $pension_id)->where('scheme_id', $scheme_id)/*->where('bank_code', $acc_no)->where('bank_ifsc', $ifsc)->where('mobile_no', $mob_no)*/->update(['lot_generated' => -1]);    ////Gaurav Help  substr("3161613",4);
							//18nov						DB::table('ben_export')->where('pension_id', $pension_id)->where('drn_part', $DRN_part)->where('scheme_id', $scheme_id)->where('acc_no', $acc_no)->where('ifsc', $ifsc)->where('mobile_no', $mob_no)->update(['ifms_status' => $val2[$j], 'ifms_ref_no' => 0, 'wrongdata_flag' => 1, 'updated_at' => DB::raw("now()") /*, 'paid_yymm' => 0*/]);
							DB::connection('pgsql_paywrite')->table('ifms.transaction_lot_details')->where('pension_id', $pension_id)->where('drn_part', $DRN_part)->where('scheme_id', $scheme_id)/*->where('acc_no', $acc_no)->where('ifsc', $ifsc)->where('mobile_no', $mob_no)*/->update(['ifms_status' => $val2[$j], 'ifms_ref_no' => 0, 'wrongdata_flag' => 1, 'updated_at' => DB::raw("now()"), 'status_code' => 'IF', 'pmt_status' => false /*, 'paid_yymm' => 0*/]);
						}
						$j = $j + 1;
					}

					$i = $i + 1;
				}
				//echo ' Wrong Data Count: '.$i;
				DB::connection('pgsql_paywrite')->table('payment.lot_master')->where('lot_no', $DRN_part)->where('scheme_id', $scheme_id)->update(['ifms_wrongdata_count' => $i]);
				DB::connection('pgsql_paywrite')->table('ifms.transaction_lot')->where('lot_no', $DRN_part)->where('scheme_id', $scheme_id)->update(['ifms_wrongdata_count' => $i]);

				$return_status =  $i . ' Wrong Data Collected ';
			} else {
				$REASON = $remote_xml_file->REASON;
				//echo 'REASON: '.$REASON;

				DB::connection('pgsql_paywrite')->table('payment.lot_master')->where('lot_no', $DRN_part)->where('scheme_id', $scheme_id)->update(['lot_status' => 1]);
				DB::connection('pgsql_paywrite')->table('ifms.transaction_lot')->where('lot_no', $DRN_part)->where('scheme_id', $scheme_id)->update(['lot_status' => 10]);
				//18nov			DB::table('ben_export')->where('drn_part', $DRN_part)->where('scheme_id', $scheme_id)->where('is_active','<>',0)->update(['ifms_status' => $REASON]);
				DB::connection('pgsql_paywrite')->table('ifms.transaction_lot_details')->where('drn_part', $DRN_part)->where('scheme_id', $scheme_id)->where('is_active', '<>', 0)->update(['ifms_status' => $REASON]);

				return $REASON;
			}
		} else {
			//echo ' Wrong Data File Not Exist ';
		}
		$updateCount3 = DB::connection('pgsql_paywrite')->statement("update ifms.temp_lot_master set rbi_sent_count=(select count(*) from ifms.transaction_lot_details where drn_part='" . $lot_no . "' and scheme_id=" . $scheme_id . " and is_active<>0 and wrongdata_flag=0)
							where scheme_id=" . $scheme_id . " and lot_no='" . $lot_no . "'");
		//echo ' update count3 : '.$updateCount3.'</br>';

		if ($return_status) {
			return $return_status;
		} else {
			return 'No Wrong Data File Received ';
		}
	}
}

