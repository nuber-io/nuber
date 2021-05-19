<?php
/**
 * Nuber.io
 * Copyright 2020 - 2021 Jamiel Sharief.
 *
 * SPDX-License-Identifier: AGPL-3.0
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.nuber.io
 * @license     https://opensource.org/licenses/AGPL-3.0 AGPL-3.0 License
 */
declare(strict_types = 1);
namespace App\Lxd\Endpoint;

use App\Lxd\Endpoint;

/**
 * $ lxc config trust --help
 * [X] add         Add new trusted clients
 * [X] list        List trusted clients
 * [X] remove      Remove trusted clients
 */
class Certificate extends Endpoint
{
    /**
     * This will generate the certificate and private key. Not part of API.
     *
     * @param string $path
     * @param array $options These are options
     * @return bool
     */
    public static function generate(string $path, array $options = []): bool
    {
        $options += [
            'csr' => [
                //'countryName' => 'GB',
                //'stateOrProvinceName' => 'London',
                //'localityName' => 'London',
                //'organizationName' => 'PHP Dev',
                'commonName' => '127.0.0.1',
            ]
 
        ];

        $privateKey = openssl_pkey_new([
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $certificate = openssl_csr_new($options['csr'], $privateKey);
        $certificate = openssl_csr_sign($certificate, null, $privateKey, 365);

        // Export Cert
        openssl_x509_export($certificate, $certificateOutput);
        openssl_pkey_export($privateKey, $privateKeyOutput);

        return (bool) file_put_contents($path . '/certificate', $certificateOutput) && (bool) file_put_contents($path . '/privateKey', $privateKeyOutput);
    }

    /**
     * Gets the current certificate status
     *
     * @return string trusted|untrusted
     */
    public function status(): string
    {
        return $this->sendGetRequest('')['auth'];
    }

    /**
     * Gets a list of certificates
     *
     * @example lxc config trust list
     *
     * @param array $options
     *  - recursive: default: 1. level of recursion e.g 0-2
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest('/certificates', [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);

        return $this->removeEndpoints(
            $response,
            '/1.0/certificates/'
        );
    }

    /**
     * Gets information on the certificate
     *
     * @param string $fingerprint
     * @return array
     */
    public function get(string $fingerprint): array
    {
        return $this->sendGetRequest("/certificates/{$fingerprint}");
    }

    /**
     * Adds a trusted certificate (uses from the current connection)
     *
     * @see https://lxd.readthedocs.io/en/latest/rest-api/#10certificates
     *
     * @param string $password
     * @return void
     */
    public function add(string $password): void
    {
        $this->sendPostRequest('/certificates', [
            'data' => [
                'type' => 'client',
                'name' => 'nuber',
                'password' => $password
            ]
        ]);
    }

    /**
    *  Remove a trusted certificate
    *
    * @param string $fingerprint fingerprint
    * @return void
    */
    public function remove(string $fingerprint): void
    {
        $this->sendDeleteRequest("/certificates/{$fingerprint}");
    }
}
