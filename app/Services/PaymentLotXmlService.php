<?php

namespace App\Services;

use DOMDocument;
use App\Models\PaymentLotMaster;
use App\Models\SbiTransactionLotDetail;
use App\Helpers\XmlSigner;
use App\Helpers\SBIEncryptDecrypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PaymentLotXmlService
{
    public $sbi_sftp_server;
    public $dec_privateKey;
    public $enc_publickey;
    public function __construct()
    {
        // Read SFTP server from application settings (config/app.php)
        $this->sbi_sftp_server = config('app.sbi_sftp_server');

        // RSA KEY FOR ENCRYPTION
        $this->dec_privateKey = file_exists(storage_path('app/cert_enc/jb-private-key.pem')) ? file_get_contents(storage_path('app/cert_enc/jb-private-key.pem')) : null;
        $this->enc_publickey = file_exists(storage_path('app/cert_enc/sbi-public-key.pem')) ? file_get_contents(storage_path('app/cert_enc/sbi-public-key.pem')) : null;
    }

    public function generateAndSignXml(PaymentLotMaster $lotMaster)
    {
        $scheme_id = $lotMaster->scheme_id;
        $schemeName = \App\Models\Scheme::find($scheme_id)?->name ?? 'SCHEME';

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

        $debit_account = $xmlFile->createElement("DEBIT_ACCOUNT");
        // Using defaults or extracting from existing schema, since we don't have all fields directly in SbiTransactionLotDetail
        $debit_account->setAttribute("ACCOUNT_DEBIT", 'DEFAULT_ACCOUNT');
        $debit_account->setAttribute("BANK_NAME", "STATE BANK OF INDIA");
        $debit_account->setAttribute("CREDIT_COUNT", $ben_data->count());
        $debit_account->setAttribute("DEBIT_AMOUNT", $lotMaster->total_amount);
        $debit_account->setAttribute("DEBIT_REFERENCE", $firstBen->debit_reference ?? 'DR_' . $lotMaster->lot_no);
        $debit_account->setAttribute("IFSC_CODE_DEBIT", 'DEFAULT_IFSC');
        $debit_account->setAttribute("TRAN_DATE", now()->format('Y-m-d'));
        $debit_account->setAttribute("AGENCY_DR_REF", 'AG_DR_' . $lotMaster->lot_no);
        $debit_account->setAttribute("DEBIT_NARRATION", 'Payment for ' . $schemeName);
        $debit_account->setAttribute("STATE", "WB");
        $debit_account->setAttribute("EMAIL", "finance@gov.in");
        $state_gov_payments->appendChild($debit_account);

        $credit_accounts = $xmlFile->createElement("CREDITACCOUNTS");
        $debit_account->appendChild($credit_accounts);

        foreach ($ben_data as $details) {
            $credit_account = $xmlFile->createElement("CREDIT_ACCOUNT");
            $credit_account->setAttribute("ACCOUNT_CREDIT", trim($details->accno ?? ''));
            $credit_account->setAttribute("CREDIT_AMOUNT", $details->amount_rs);
            $credit_account->setAttribute("CREDIT_REFERENCE", trim($details->debit_reference ?? ''));
            $credit_account->setAttribute("IFSC_CODE_CREDIT", trim($details->ifsc ?? ''));
            $credit_account->setAttribute("NAME", substr(trim($details->ben_name ?? ''), 0, 50));
            $credit_account->setAttribute("PAYMENT_MODE", "A");
            $credit_account->setAttribute("NPCI_USER_ID", trim($details->npci_user_id ?? ''));
            $credit_account->setAttribute("NPCI_USER_NAME", trim($details->npci_user_name ?? ''));
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
    public function pushToSBI(PaymentLotMaster $lotMaster)
    {
          $debitRef = $lotMaster->file_name;
          $file_name = $debit_ref . '.xml';
          $storagePath = 'app/sbi/ePay/ToProcess/' . $file_name;
          $storagePathEnc = 'app/sbi/ePay/ToProcessEnc/' . $file_name;
          $payment_file_content_without_enc = file_get_contents(storage_path($storagePath));
          $payment_file_content = file_get_contents(storage_path($storagePathEnc));
          Storage::disk($this->sbi_sftp_server)->put('ePay/ToProcess/' . $file_name, $payment_file_content);
    }
}
