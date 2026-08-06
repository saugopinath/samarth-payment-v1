<?php

namespace App\Services\Integration\SBI;

use DOMDocument;
use App\Models\PaymentLotMaster;
use App\Models\SbiTransactionLotDetail;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\SbiPaymentLotMasterAdditionalInfo;
use App\Services\Contracts\PaymentIntegrationInterface;
use App\Helpers\XmlSigner;
use App\Helpers\SBIEncryptDecrypt;

class SbiSftpIntegrationService implements PaymentIntegrationInterface
{
    private static $instance = null;
    
    public $sbi_sftp_server;
    public $dec_privateKey;
    public $enc_publickey;

    private function __construct()
    {
        // Read SFTP server from application settings (config/app.php)
        $this->sbi_sftp_server = config('app.sbi_sftp_server');

        // RSA KEY FOR ENCRYPTION
        $this->dec_privateKey = file_exists(storage_path('app/cert_enc/jb-private-key.pem')) ? file_get_contents(storage_path('app/cert_enc/jb-private-key.pem')) : null;
        $this->enc_publickey = file_exists(storage_path('app/cert_enc/sbi-public-key.pem')) ? file_get_contents(storage_path('app/cert_enc/sbi-public-key.pem')) : null;
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new SbiSftpIntegrationService();
        }
        return self::$instance;
    }

    public function preparePayload(PaymentLotMaster $lotMaster)
    {
        $scheme_id = $lotMaster->scheme_id;
        $schemeName = \App\Models\Scheme::find($scheme_id)?->name ?? 'SCHEME';

        $setting = \App\Models\PaymentMainSetting::where('scheme_id', $scheme_id)
            ->where('financial_year', $lotMaster->lot_year)
            ->first();

        $paymentType = null;
        $aadharType = null;
        if ($setting) {
            $monthField = strtolower($lotMaster->lot_month);
            $monthData = $setting->$monthField;
            if (is_array($monthData) && isset($monthData['payment_type'])) {
                $paymentType = $monthData['payment_type'];
            }
            if (is_array($monthData) && isset($monthData['aadhar_type'])) {
                $aadharType = $monthData['aadhar_type'];
            }
        }

        // Get beneficiaries
        $ben_data = SbiTransactionLotDetail::where('lot_no', $lotMaster->lot_no)
            ->where('scheme_id', $scheme_id)
            ->get();

        if ($ben_data->isEmpty()) {
            throw new \Exception("No beneficiaries found for this lot.");
        }

        $xmlFile = new DOMDocument("1.0", 'UTF-8');
        $xmlFile->formatOutput = true;
        $xmlFile->xmlStandalone = false;

        $state_gov_payments = $xmlFile->createElement("STATE_GOVT_PAYMENTS");
        $xmlFile->appendChild($state_gov_payments);

        // We assume we can get these from the first beneficiary or the lot itself
        $firstBen = $ben_data->first();

        $sbiSetting = \App\Models\SbiPaymentSetting::where('scheme_id', $lotMaster->scheme_id)->first() 
            ?? \App\Models\SbiPaymentSetting::whereNull('scheme_id')->first();

        $debit_account = $xmlFile->createElement("DEBIT_ACCOUNT");
        // Using defaults or extracting from existing schema, since we don't have all fields directly in SbiTransactionLotDetail
        if (app()->environment('production')) {
            $debit_account->setAttribute("ACCOUNT_DEBIT", $sbiSetting->bank_account_no ?? 'DEFAULT_ACCOUNT');
            $debit_account->setAttribute("IFSC_CODE_DEBIT", $sbiSetting->ifsc_code ?? 'DEFAULT_IFSC');
            $debit_account->setAttribute("EMAIL", $sbiSetting->email ?? "finance@gov.in");
        } else {
            $debit_account->setAttribute("ACCOUNT_DEBIT", 'STATIC_ACCOUNT_DEBIT');
            $debit_account->setAttribute("IFSC_CODE_DEBIT", 'STATIC_IFSC');
            $debit_account->setAttribute("EMAIL", 'static@test.com');
        }

        $debit_account->setAttribute("BANK_NAME", "STATE BANK OF INDIA");
        $debit_account->setAttribute("CREDIT_COUNT", $ben_data->count());
        $debit_account->setAttribute("DEBIT_AMOUNT", $lotMaster->total_amount);
        $debit_account->setAttribute("DEBIT_REFERENCE", $firstBen->debit_reference ?? 'DR_' . $lotMaster->lot_no);
        $debit_account->setAttribute("TRAN_DATE", now()->format('Y-m-d'));
        $debit_account->setAttribute("AGENCY_DR_REF", 'AG_DR_' . $lotMaster->lot_no);
        $debit_account->setAttribute("DEBIT_NARRATION", 'Payment for ' . $schemeName);
        $debit_account->setAttribute("STATE", "WB");
        $state_gov_payments->appendChild($debit_account);

        $credit_accounts = $xmlFile->createElement("CREDITACCOUNTS");
        $debit_account->appendChild($credit_accounts);

        foreach ($ben_data as $details) {
            $credit_account = $xmlFile->createElement("CREDIT_ACCOUNT");
            
            if ($paymentType === '5001') {
                $credit_account->setAttribute("ACCOUNT_CREDIT", trim($details->accno ?? ''));
                $credit_account->setAttribute("IFSC_CODE_CREDIT", trim($details->ifsc ?? ''));
            } elseif ($paymentType === '5002') {
                $aadharValue = trim($details->aadhar_no ?? '');
                if (($aadharType ?? 'raw') === 'token' && !empty($aadharValue)) {
                    $aadharValue = $this->detokenizeAadhar($aadharValue);
                }
                $credit_account->setAttribute("AADHAAR_NO", $aadharValue);
            }

            $credit_account->setAttribute("CREDIT_AMOUNT", $details->amount_rs);
            $credit_account->setAttribute("CREDIT_REFERENCE", trim($details->debit_reference ?? ''));
            $credit_account->setAttribute("NAME", substr(trim($details->ben_name ?? ''), 0, 50));
            $credit_account->setAttribute("PAYMENT_MODE", $paymentType === '5001' ? "A" : ($paymentType === '5002' ? "U" : ""));
            if (app()->environment('production')) {
                $credit_account->setAttribute("NPCI_USER_ID", $sbiSetting->npci_user_id ?? trim($details->npci_user_id ?? ''));
                $credit_account->setAttribute("NPCI_USER_NAME", $sbiSetting->npci_user_name ?? trim($details->npci_user_name ?? ''));
            } else {
                $credit_account->setAttribute("NPCI_USER_ID", "STATIC_NPCI_ID");
                $credit_account->setAttribute("NPCI_USER_NAME", "STATIC_NPCI_NAME");
            }
            $credit_account->setAttribute("AGENCY_CR_REF", trim($details->agency_cr_ref ?? ''));
            $credit_account->setAttribute("NARRATION", substr(trim($schemeName), 0, 20));
            $credit_accounts->appendChild($credit_account);
            $credit_account = null;
        }

        $debitRef = $firstBen->debit_reference ?? 'DR_' . $lotMaster->lot_no;

        // Ensure directories exist
        File::ensureDirectoryExists(storage_path('app/sbi/unsigned'));
        File::ensureDirectoryExists(storage_path('app/sbi/signed'));
        File::ensureDirectoryExists(storage_path('app/sbi/ePay/ToProcess'));
        File::ensureDirectoryExists(storage_path('app/sbi/ePay/ToProcessEnc'));

        $unsigned_xml_file = storage_path('app/sbi/unsigned/') . $debitRef . ".xml";
        $xmlFile->save($unsigned_xml_file);

        $signed_xml_file = storage_path('app/sbi/signed/') . $debitRef . ".xml";
        // Read certificate details from application settings (config/app.php)
        $cert = config('app.sbi_cert_path');
        $certPass = config('app.sbi_cert_pass');
        
        $xmlSigner = new XmlSigner();
        if (file_exists($cert)) {
            $xmlSigner->loadPfxFile($cert, $certPass);
        } else {
            \Log::warning("SBI XML signing certificate not found at path: {$cert}");
        }
        $xmlSigner->setReferenceUri('');
        
        // Use a generic DigestAlgorithmType::SHA1 string/constant since we don't have the original class
        $xmlSigner->signXmlFile($unsigned_xml_file, $signed_xml_file, 'SHA1');

        $file_name = $debitRef . '.xml';
        
        // Copy to ToProcess
        copy($signed_xml_file, storage_path('app/sbi/ePay/ToProcess/' . $file_name));

        // Encrypt and store
        $file_content = file_get_contents(storage_path('app/sbi/ePay/ToProcess/' . $file_name));
        $encryptedFile = SBIEncryptDecrypt::file_encrypt($file_content, $this->enc_publickey);
        file_put_contents(storage_path('app/sbi/ePay/ToProcessEnc/' . $file_name), $encryptedFile);

        return [
            'unsigned' => $unsigned_xml_file,
            'signed' => $signed_xml_file,
            'encrypted' => storage_path('app/sbi/ePay/ToProcessEnc/' . $file_name)
        ];
    }
    public function pushToTarget(SbiPaymentLotMasterAdditionalInfo $lotMasterAdd)
    {
          $debitRef = trim($lotMasterAdd->debit_reference);
          $file_name = $debitRef.'.xml';
          $storagePath = 'app/sbi/ePay/ToProcess/'.$file_name;
          $storagePathEnc = 'app/sbi/ePay/ToProcessEnc/'.$file_name;
          $payment_file_content_without_enc = file_get_contents(storage_path($storagePath));
          $payment_file_content = file_get_contents(storage_path($storagePathEnc));
          $remotePath = app()->environment('production') ? 'ePay/ToProcess/' : 'ePay/Test/ToProcess/';
          
          Storage::disk($this->sbi_sftp_server)->put($remotePath . $file_name, $payment_file_content);

          if (app()->environment('local')) {
              $ack_xml = '<?xml version="1.0" encoding="utf-8" standalone="no"?>
<PAYMENT_ACK>
<DEBIT_ACCOUNT ACK_DATE="' . date('dmY') . '" ACK_REMARKS="Success" ACK_STATUS_CODE="000" DEBIT_REFERENCE="' . $debitRef . '"/>
</PAYMENT_ACK>';
              
              // Note: Using enc_publickey to mock the encrypted file, assuming the testing environment has compatible keys configured.
              $encryptedAck = SBIEncryptDecrypt::file_encrypt($ack_xml, $this->enc_publickey);
              Storage::disk($this->sbi_sftp_server)->put('ePay/Acknowledgement/' . $debitRef . '_ACK.xml', $encryptedAck);
          }
    }

    public function checkAcknowledge(PaymentLotMaster $lotMaster)
    {
        
        $debitRef = trim($lotMaster->sbiPaymentLotMasterAdditionalInfo->debit_reference);
        $ackfile_name = $debitRef . '_ACK.xml';

        $exists = Storage::disk($this->sbi_sftp_server)->exists('ePay/Acknowledgement/' . $ackfile_name);
        
        if ($exists) {
            $remote_file = Storage::disk($this->sbi_sftp_server)->get('ePay/Acknowledgement/' . $ackfile_name);
            Storage::put('sbi/ePay/AcknowledgementEnc/' . $ackfile_name, $remote_file);
            
            $decryptedFile = SBIEncryptDecrypt::file_decrypt($remote_file, $this->dec_privateKey);
            Storage::put('sbi/ePay/Acknowledgement/' . $ackfile_name, $decryptedFile);
            
            $remote_xml_file = simplexml_load_string($decryptedFile);
            $ack_remarks = (string) $remote_xml_file->DEBIT_ACCOUNT['ACK_REMARKS'];
            $file_ack_status_code = (string) $remote_xml_file->DEBIT_ACCOUNT['ACK_STATUS_CODE'];
            
            if ($file_ack_status_code == '000') {
                $lotMaster->cur_status = '52105'; // GENERATED,PUSHED AND RESPONSE RECEIVED
                $lotMaster->response_receive_date = now();
                $lotMaster->save();

                // Mock the response file for local environment testing
                if (app()->environment('local')) {
                    $ben_data = SbiTransactionLotDetail::where('lot_no', $lotMaster->lot_no)->get();
                    $mockResponse = '<?xml version="1.0" encoding="UTF-8" standalone="no"?><PAYMENT_RESPONSE>';
                    $mockResponse .= '<DEBIT_ACCOUNT ACCOUNT_DEBIT="123456789" AGENCY_DR_REF="477970" AMOUNT_DEBIT="'.$lotMaster->total_amount.'" DEBIT_JOURNAL="SLIGQ29072606212930800000" ';
                    $mockResponse .= 'DEBIT_REFERENCE="'.$debitRef.'" DEBIT_REMARKS="SUCCESS" DEBIT_STATUS_CODE="S00" DISTRICT="" IFSC_CODE_DEBIT="SBIN0012360" STATE="WB" TRAN_DATE="'.date('dmY').'"/>';
                    $mockResponse .= '<CREDITACCOUNTS>';
                    foreach ($ben_data as $ben) {
                        // 90% chance of success, 10% chance of failure for mocking
                        $isSuccess = rand(1, 10) <= 9;
                        $statusCode = $isSuccess ? "S00" : "E01";
                        $remarks = $isSuccess ? "00-Success" : "Invalid Account/Mock Failure";

                        $mockResponse .= '<CREDIT_ACCOUNT AADHAAR_NO="'.trim($ben->aadhar_no ?? '').'" ACCOUNT_CREDIT="'.trim($ben->accno ?? '').'" AGENCY_CR_REF="'.trim($ben->agency_cr_ref ?? '').'" CREDIT_AMOUNT="'.$ben->amount_rs.'" CREDIT_BANK_IIN="" ';
                        $mockResponse .= 'CREDIT_PAYMENT_REFERENCE="'.rand(1000000000, 9999999999).'" CREDIT_REFERENCE="'.trim($ben->debit_reference ?? '').'" CREDIT_REMARKS="'.$remarks.'" CREDIT_STATUS_CODE="'.$statusCode.'" CREDIT_TRANSACTION_REFERENCE="'.rand(100000000000, 999999999999).'" ';
                        $mockResponse .= 'CREDIT_TRAN_DATE="'.date('dmY').'" IFSC_CODE_CREDIT="'.trim($ben->ifsc ?? '').'" NAME="'.trim($ben->ben_name ?? '').'" PAYMENT_MODE="A"/>';
                    }
                    $mockResponse .= '</CREDITACCOUNTS></PAYMENT_RESPONSE>';

                    $encryptedRes = SBIEncryptDecrypt::file_encrypt($mockResponse, $this->enc_publickey);
                    Storage::disk($this->sbi_sftp_server)->put('ePay/Response/' . $debitRef . '.xml', $encryptedRes);
                }

                return [
                    'status' => 1,
                    'msg' => 'Lot No:- <b>' . $lotMaster->lot_no . '</b> acknowledgement has been received from SBI successfully. ' . $ack_remarks,
                    'type' => 'green'
                ];
            } else {
                return [
                    'status' => 3,
                    'msg' => 'Acknowledgement error from SBI for Lot - <b>' . $lotMaster->lot_no . '</b> with Remarks - ' . $ack_remarks,
                    'type' => 'blue'
                ];
            }
        } else {
            return [
                'status' => 4,
                'msg' => 'Acknowledgement file is not generated in SBI server for Lot - <b>' . $lotMaster->lot_no . '</b>',
                'type' => 'blue'
            ];
        }
    }

    public function checkResponse(PaymentLotMaster $lotMaster)
    {
        try {
            $debitRef = trim($lotMaster->sbiPaymentLotMasterAdditionalInfo->debit_reference);
            // Sometimes responses come back as .xml or _RES.xml. We'll use .xml based on SBI standard.
            // dd($debitRef);
            $resfile_name = $debitRef . '.xml';

            $exists = Storage::disk($this->sbi_sftp_server)->exists('ePay/Response/' . $resfile_name);
            
            if ($exists) {
                $setting = \App\Models\PaymentMainSetting::where('scheme_id', $lotMaster->scheme_id)
                        ->where('financial_year', $lotMaster->lot_year)
                        ->first();

                    $paymentType = null;
                    $aadharType = null;
                    if ($setting) {
                        $monthField = strtolower($lotMaster->lot_month);
                        $monthData = $setting->$monthField;
                        if (is_array($monthData) && isset($monthData['payment_type'])) {
                            $paymentType = $monthData['payment_type'];
                        }
                        if (is_array($monthData) && isset($monthData['aadhar_type'])) {
                            $aadharType = $monthData['aadhar_type'];
                        }
                    }
               
                $remote_file = Storage::disk($this->sbi_sftp_server)->get('ePay/Response/' . $resfile_name);
                Storage::put('sbi/ePay/ResponseEnc/' . $resfile_name, $remote_file);
                
                $decryptedFile = SBIEncryptDecrypt::file_decrypt($remote_file, $this->dec_privateKey);
                Storage::put('sbi/ePay/Response/' . $resfile_name, $decryptedFile);
                
                $remote_xml_file = simplexml_load_string($decryptedFile);
                
                if ($remote_xml_file === false) {
                    throw new \Exception('Failed to parse XML response from SBI.');
                }
                
                $debit_status_code = (string) $remote_xml_file->DEBIT_ACCOUNT['DEBIT_STATUS_CODE'];
                $debit_remarks = (string) $remote_xml_file->DEBIT_ACCOUNT['DEBIT_REMARKS'];
                
                if ($debit_status_code == 'S00') {
                     
                    $successCount = 0;
                    $failedCount = 0;
                    $successAmount = 0;
                    $failedAmount = 0;

                    // Loop through CREDITACCOUNTS
                    if (isset($remote_xml_file->CREDITACCOUNTS->CREDIT_ACCOUNT)) {
                        foreach ($remote_xml_file->CREDITACCOUNTS->CREDIT_ACCOUNT as $credit_account) {
                            $credit_ref = (string) $credit_account['CREDIT_REFERENCE'];
                            $credit_status = (string) $credit_account['CREDIT_STATUS_CODE'];
                            $credit_remarks = (string) $credit_account['CREDIT_REMARKS'];
                            $credit_tran_ref = (string) $credit_account['CREDIT_TRANSACTION_REFERENCE'];
                            $credit_tran_date = (string) $credit_account['CREDIT_TRAN_DATE'];
                            $credit_amount = (float) $credit_account['CREDIT_AMOUNT'];
                            
                            // Update SbiTransactionLotDetail
                            $detail = SbiTransactionLotDetail::where('lot_no', $lotMaster->lot_no)
                                ->where('agency_cr_ref', $credit_account['AGENCY_CR_REF'])
                                ->first();

                            if ($detail) {
                                $codemasterStatus = \App\Models\Codemaster::where('short_name', $credit_status)
                                    ->where('parent_short_code', 'sbi_status_code')
                                    ->first();
                                $mapped_status_code = $codemasterStatus ? $codemasterStatus->code : $credit_status;

                                $detail->status_code = $mapped_status_code; // Map to our internal codemaster code
                                $detail->remarks = $credit_remarks;
                                $detail->credit_payment_reference = $credit_tran_ref;
                                // Add other fields if required by your DB schema
                                $detail->save();

                                if ($credit_status == 'S00') {
                                    $successCount++;
                                    $successAmount += $credit_amount;
                                } else {
                                    $failedCount++;
                                    $failedAmount += $credit_amount;

                                    \App\Models\FailedPaymentDetail::Create([
                                        'lot_no' => $lotMaster->lot_no,
                                        'ben_id' => $detail->ben_id,
                                        'scheme_id' => $lotMaster->scheme_id,
                                        'status_code' => $mapped_status_code,
                                        'remarks' => $credit_remarks,
                                        'failed_type' => '1415',
                                        'failed_source' => '5201'
                                    ]);
                                }
                            }
                        }
                    }

                    $lotMaster->success_count = $successCount;
                    $lotMaster->failed_count = $failedCount;
                    $lotMaster->success_amount = $successAmount;
                    $lotMaster->failed_amount = $failedAmount;
                    // If everything is processed, you might want a distinct status for it. Here we keep it as 52105 or update it to something else
                    $lotMaster->cur_status = '52106'; // Keep or change based on your workflow
                    $lotMaster->save();

                    return [
                        'status' => 1,
                        'msg' => 'Response for Lot No:- <b>' . $lotMaster->lot_no . '</b> received successfully. Success: ' . $successCount . ', Failed: ' . $failedCount,
                        'type' => 'green'
                    ];
                } else {
                    return [
                        'status' => 3,
                        'msg' => 'Debit processing failed for Lot - <b>' . $lotMaster->lot_no . '</b> with Remarks - ' . $debit_remarks,
                        'type' => 'red'
                    ];
                }
            } else {
                return [
                    'status' => 4,
                    'msg' => 'Response file is not generated in SBI server for Lot - <b>' . $lotMaster->lot_no . '</b>',
                    'type' => 'blue'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 4,
                'msg' => 'Error processing response: ' . $e->getMessage(),
                'type' => 'red'
            ];
        }
    }

    /**
     * Call external API to detokenize an Aadhar token.
     *
     * @param string $token
     * @return string
     */
    private function detokenizeAadhar(string $token): string
    {
        if (app()->environment(['local', 'staging'])) {
            return '666666666666';
        }

        // TODO: Replace with actual external Aadhar detokenize API call
        // Example:
        // $response = \Illuminate\Support\Facades\Http::post('https://external-api.gov.in/detokenize', ['token' => $token]);
        // return $response->json('aadhar_no');

        return $token; // Placeholder: returning token as raw until API is integrated
    }
}
