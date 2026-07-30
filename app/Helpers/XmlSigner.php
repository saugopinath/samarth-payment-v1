<?php

namespace App\Helpers;

use DOMDocument;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Exception;

class XmlSigner
{
    private $pfxPath;
    private $password;

    public function loadPfxFile($cert, $password)
    {
        $this->pfxPath = $cert;
        $this->password = $password;
    }

    public function setReferenceUri($uri)
    {
        // Currently ignoring URI for enveloped signature
    }

    public function signXmlFile($unsignedFile, $signedFile, $digestAlgorithm)
    {
        if (!class_exists('RobRichards\XMLSecLibs\XMLSecurityDSig')) {
            $basePath = base_path('vendor/robrichards/xmlseclibs/src/');
            if (file_exists($basePath . 'Utils/XPath.php')) {
                require_once $basePath . 'Utils/XPath.php';
                require_once $basePath . 'XMLSecurityKey.php';
                require_once $basePath . 'XMLSecurityDSig.php';
            } else {
                throw new Exception("XMLSecLibs is not installed correctly.");
            }
        }

        $doc = new DOMDocument();
        $doc->load($unsignedFile);

        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        $objDSig->addReference(
            $doc,
            XMLSecurityDSig::SHA1,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
        );

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);

        $certs = [];
        $pkcs12 = file_get_contents($this->pfxPath);
        if (!openssl_pkcs12_read($pkcs12, $certs, $this->password)) {
            throw new Exception("Could not read PFX file. Check path and password.");
        }

        $objKey->loadKey($certs['pkey']);

        $objDSig->sign($objKey);
        
        // Add the certificate to the signature
        $objDSig->add509Cert($certs['cert']);

        // Append the signature to the root node
        $objDSig->appendSignature($doc->documentElement);

        $doc->save($signedFile);
    }
}
