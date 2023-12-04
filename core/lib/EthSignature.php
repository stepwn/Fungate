<?php


spl_autoload_register(function ($class_name) {
    // Replace backslashes in the namespace with forward slashes for the file path
    $class_name = str_replace('\\', '/', $class_name);
    $file = __DIR__ . '/' . $class_name . '.php';
    //error_log($file); // Log the file path for debugging
    if (file_exists($file)) {
        require_once $file;
    }
});
use Elliptic\EC;
use kornrunner\Keccak;

class EthSignature{

    private function pubKeyToAddress($pubkey) {
        return "0x" . substr(Keccak::hash(substr(hex2bin($pubkey->encode("hex")), 1), 256), 24);
    }

    public function verify($message, $signature, $address) {
        
        $msglen = strlen($message);
        $hash   = Keccak::hash("\x19Ethereum Signed Message:\n{$msglen}{$message}", 256);
        $sign   = ["r" => substr($signature, 2, 64), "s" => substr($signature, 66, 64)];
        $recid  = ord(hex2bin(substr($signature, 130, 2))) - 27; 
        if ($recid != ($recid & 1)) return false;
        $ec = new EC('secp256k1');
        $pubkey = $ec->recoverPubKey($hash, $sign, $recid);
        
        return strtolower($address) == strtolower($this->pubKeyToAddress($pubkey));
    }

}


