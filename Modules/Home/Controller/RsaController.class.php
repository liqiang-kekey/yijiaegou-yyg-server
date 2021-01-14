<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2020/9/9
 * Time: 14:18
 */

namespace Home\Controller;


class RsaController {

    const RSA_ALGORITHM_KEY_TYPE = OPENSSL_KEYTYPE_RSA;
    const RSA_ALGORITHM_SIGN = OPENSSL_ALGO_SHA256;

    private $_config = [
        'public_key' => '',
        'private_key' => '',
        'key_len' => '',
    ];

    public function __construct($private_key_file_path, $public_key_file_path)
    {
        $this->_config['private_key'] = $this->_getContents($private_key_file_path);
        $this->_config['public_key'] = $this->_getContents($public_key_file_path);
        $this->_config['key_len'] = $this->_getKenLen();
    }

    /**
     * @uses 获取文件内容
     * @param $file_path string
     * @return bool|string
     */
    private function _getContents($file_path)
    {
        file_exists($file_path) or die ('密钥或公钥的文件路径错误');
        return file_get_contents($file_path);
    }

    private function _getKenLen()
    {
        $pub_id = openssl_get_publickey($this->_config['public_key']);
        return openssl_pkey_get_details($pub_id)['bits'];
    }

    /**
     * @uses 获取私钥
     * @return bool|resource
     */
    private function _getPrivateKey()
    {
        $private_key = $this->_config['private_key'];
        return openssl_pkey_get_private($private_key);
    }

    /**
     * @uses 获取公钥
     * @return bool|resource
     */
    private function _getPublicKey()
    {
        $public_key = $this->_config['public_key'];

        return openssl_pkey_get_public($public_key);
    }

    /**
     * @uses 私钥加密
     * @param string $data
     * @return null|string
     */
    public function privateEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }

        $encrypted = '';
        $part_len = $this->_config['key_len'] / 8 - 11;

        $parts = str_split($data, $part_len);
        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_private_encrypt($part, $encrypted_temp, $this->_getPrivateKey());
            $encrypted .= $encrypted_temp;
        }

        return base64_encode($encrypted);

        //return openssl_private_encrypt($data, $encrypted, $this->_getPrivateKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * @uses 公钥加密
     * @param string $data
     * @return null|string
     */
    public function publicEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }

        $encrypted = '';
        $part_len = $this->_config['key_len'] / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_public_encrypt($part, $encrypted_temp, $this->_getPublicKey());
            $encrypted .= $encrypted_temp;
        }

        return base64_encode($encrypted);

        //return openssl_public_encrypt($data, $encrypted, $this->_getPublicKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * @uses 私钥解密
     * @param string $encrypted
     * @return null
     */
    public function privateDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }

        $decrypted = "";
        $part_len = $this->_config['key_len'] / 8;
        $base64_decoded = base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_private_decrypt($part, $decrypted_temp,$this->_getPrivateKey());
            $decrypted .= $decrypted_temp;
        }

        return $decrypted;

        //return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->_getPrivateKey())) ? $decrypted : null;
    }

    /**
     * @uses 公钥解密
     * @param string $encrypted
     * @return null
     */
    public function publicDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }

        $decrypted = "";
        $part_len = $this->_config['key_len'] / 8;
        $base64_decoded = base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_public_decrypt($part, $decrypted_temp,$this->_getPublicKey());
            $decrypted .= $decrypted_temp;
        }

        return $decrypted;

        //return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, $this->_getPublicKey())) ? $decrypted : null;
    }

    /*
     * 数据加签
     */
    public function sign($data)
    {
        openssl_sign($data, $sign, $this->_getPrivateKey(), self::RSA_ALGORITHM_SIGN);

        return base64_encode($sign);
    }

    /*
     * 数据签名验证
     */
    public function verify($data, $sign)
    {
        $pub_id = openssl_get_publickey($this->_getPublicKey());
        $res = openssl_verify($data, base64_decode($sign), $pub_id, self::RSA_ALGORITHM_SIGN);

        return $res;
    }
}