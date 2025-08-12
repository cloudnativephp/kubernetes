<?php

declare(strict_types=1);

namespace Kubernetes\API\CertificatesK8sIo\V1;

/**
 * CertificateSigningRequest (CSR) represents a request for a signed certificate.
 *
 * CertificateSigningRequest is used to request that a certificate be signed
 * by a denoted signer (e.g., kubernetes.io/kube-apiserver-client).
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/authentication-resources/certificate-signing-request-v1/
 */
class CertificateSigningRequest extends AbstractAbstractResource
{
    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'CertificateSigningRequest';
    }

    /**
     * Get the certificate request.
     *
     * @return string|null
     */
    public function getRequest(): ?string
    {
        return $this->spec['request'] ?? null;
    }

    /**
     * Get the signer name.
     *
     * @return string|null
     */
    public function getSignerName(): ?string
    {
        return $this->spec['signerName'] ?? null;
    }

    /**
     * Get the expiration seconds.
     *
     * @return int|null
     */
    public function getExpirationSeconds(): ?int
    {
        return $this->spec['expirationSeconds'] ?? null;
    }

    /**
     * Get the certificate usages.
     *
     * @return array<string>
     */
    public function getUsages(): array
    {
        return $this->spec['usages'] ?? [];
    }

    /**
     * Add a usage to the certificate.
     *
     * @param string $usage Certificate usage (e.g., 'digital signature', 'key encipherment')
     *
     * @return self
     */
    public function addUsage(string $usage): self
    {
        $this->spec['usages'][] = $usage;
        return $this;
    }

    /**
     * Get the username.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->spec['username'] ?? null;
    }

    /**
     * Set the UID for the certificate request.
     *
     * @param string $uid UID of the user requesting the certificate
     *
     * @return self
     */
    public function setUid(string $uid): self
    {
        $this->spec['uid'] = $uid;
        return $this;
    }

    /**
     * Get the UID.
     *
     * @return string|null
     */
    public function getUid(): ?string
    {
        return $this->spec['uid'] ?? null;
    }

    /**
     * Get the groups.
     *
     * @return array<string>
     */
    public function getGroups(): array
    {
        return $this->spec['groups'] ?? [];
    }

    /**
     * Set extra attributes for the certificate request.
     *
     * @param array<string, array<string>> $extra Extra attributes
     *
     * @return self
     */
    public function setExtra(array $extra): self
    {
        $this->spec['extra'] = $extra;
        return $this;
    }

    /**
     * Get the extra attributes.
     *
     * @return array<string, array<string>>
     */
    public function getExtra(): array
    {
        return $this->spec['extra'] ?? [];
    }

    /**
     * Get the signed certificate from status.
     *
     * @return string|null
     */
    public function getCertificate(): ?string
    {
        return $this->status['certificate'] ?? null;
    }

    /**
     * Check if the CSR is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return !$this->isApproved() && !$this->isDenied();
    }

    /**
     * Check if the CSR is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        $conditions = $this->getConditions();
        foreach ($conditions as $condition) {
            if ($condition['type'] === 'Approved' && $condition['status'] === 'True') {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the CSR conditions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConditions(): array
    {
        return $this->status['conditions'] ?? [];
    }

    /**
     * Check if the CSR is denied.
     *
     * @return bool
     */
    public function isDenied(): bool
    {
        $conditions = $this->getConditions();
        foreach ($conditions as $condition) {
            if ($condition['type'] === 'Denied' && $condition['status'] === 'True') {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper method to create a client certificate request.
     *
     * @param string        $request           Base64-encoded certificate request
     * @param string        $username          Username requesting the certificate
     * @param array<string> $groups            User groups
     * @param int           $expirationSeconds Certificate validity period
     *
     * @return self
     */
    public function createClientCertificateRequest(
        string $request,
        string $username,
        array $groups = [],
        int $expirationSeconds = 86400
    ): self {
        return $this
            ->setRequest($request)
            ->setSignerName('kubernetes.io/kube-apiserver-client')
            ->setUsername($username)
            ->setGroups($groups)
            ->setExpirationSeconds($expirationSeconds)
            ->setUsages(['client auth']);
    }

    /**
     * Set the usages for the certificate.
     *
     * @param array<string> $usages Certificate key usages (e.g., ['digital signature', 'key encipherment'])
     *
     * @return self
     */
    public function setUsages(array $usages): self
    {
        $this->spec['usages'] = $usages;
        return $this;
    }

    /**
     * Set the expiration seconds for the certificate.
     *
     * @param int $seconds Number of seconds the certificate will be valid
     *
     * @return self
     */
    public function setExpirationSeconds(int $seconds): self
    {
        $this->spec['expirationSeconds'] = $seconds;
        return $this;
    }

    /**
     * Set the groups for the certificate request.
     *
     * @param array<string> $groups Groups the user belongs to
     *
     * @return self
     */
    public function setGroups(array $groups): self
    {
        $this->spec['groups'] = $groups;
        return $this;
    }

    /**
     * Set the username for the certificate request.
     *
     * @param string $username Username requesting the certificate
     *
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->spec['username'] = $username;
        return $this;
    }

    /**
     * Set the signer name for the certificate request.
     *
     * @param string $signerName Name of the signer (e.g., kubernetes.io/kube-apiserver-client)
     *
     * @return self
     */
    public function setSignerName(string $signerName): self
    {
        $this->spec['signerName'] = $signerName;
        return $this;
    }

    /**
     * Set the certificate request in PEM format.
     *
     * @param string $request Base64-encoded PKCS#10 certificate request
     *
     * @return self
     */
    public function setRequest(string $request): self
    {
        $this->spec['request'] = $request;
        return $this;
    }

    /**
     * Helper method to create a server certificate request.
     *
     * @param string $request           Base64-encoded certificate request
     * @param string $username          Username requesting the certificate
     * @param int    $expirationSeconds Certificate validity period
     *
     * @return self
     */
    public function createServerCertificateRequest(
        string $request,
        string $username,
        int $expirationSeconds = 86400
    ): self {
        return $this
            ->setRequest($request)
            ->setSignerName('kubernetes.io/kubelet-serving')
            ->setUsername($username)
            ->setExpirationSeconds($expirationSeconds)
            ->setUsages(['digital signature', 'key encipherment', 'server auth']);
    }

    /**
     * Helper method to create a node certificate request.
     *
     * @param string $request           Base64-encoded certificate request
     * @param string $nodeName          Name of the node
     * @param int    $expirationSeconds Certificate validity period
     *
     * @return self
     */
    public function createNodeCertificateRequest(
        string $request,
        string $nodeName,
        int $expirationSeconds = 86400
    ): self {
        return $this
            ->setRequest($request)
            ->setSignerName('kubernetes.io/kube-apiserver-client-kubelet')
            ->setUsername("system:node:{$nodeName}")
            ->setGroups(['system:nodes'])
            ->setExpirationSeconds($expirationSeconds)
            ->setUsages(['digital signature', 'key encipherment', 'client auth']);
    }

    /**
     * Helper method to set common client authentication usages.
     *
     * @return self
     */
    public function setClientAuthUsages(): self
    {
        return $this->setUsages(['digital signature', 'key encipherment', 'client auth']);
    }

    /**
     * Helper method to set common server authentication usages.
     *
     * @return self
     */
    public function setServerAuthUsages(): self
    {
        return $this->setUsages(['digital signature', 'key encipherment', 'server auth']);
    }
}
