<?php
/**
 * Autoloads classes when needed.
 *
 * @param string $class_name The fully-qualified name of the class to load.
 */
spl_autoload_register(function ($class_name) {
    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    $file = __DIR__ . DIRECTORY_SEPARATOR . $class_path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Use namespaces for external libraries
use Elliptic\EC;
use kornrunner\Keccak;

/**
 * Class EthSignature handles Ethereum signature verification.
 */
class EthSignature {
    /**
     * Converts a public key to an Ethereum address.
     *
     * @param mixed $pubkey The public key.
     * @return string The Ethereum address.
     */
    private function pubKeyToAddress($pubkey) {
        return "0x" . substr(Keccak::hash(substr(hex2bin($pubkey->encode("hex")), 1), 256), 24);
    }

    /**
     * Verifies an Ethereum signature.
     *
     * @param string $message The original message signed.
     * @param string $signature The signature to verify.
     * @param string $address The Ethereum address of the signer.
     * @return bool True if the signature is valid, false otherwise.
     */
    public function verify($message, $signature, $address) {
        $msglen = strlen($message);
        $hash = Keccak::hash("\x19Ethereum Signed Message:\n{$msglen}{$message}", 256);
        $sign = ["r" => substr($signature, 2, 64), "s" => substr($signature, 66, 64)];
        $recid = ord(hex2bin(substr($signature, 130, 2))) - 27;

        if ($recid != ($recid & 1)) {
            return false;
        }

        $ec = new EC('secp256k1');
        $pubkey = $ec->recoverPubKey($hash, $sign, $recid);

        return strtolower($address) === strtolower($this->pubKeyToAddress($pubkey));
    }
}


