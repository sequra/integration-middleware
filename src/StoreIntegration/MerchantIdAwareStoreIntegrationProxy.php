<?php

namespace SeQura\Middleware\StoreIntegration;

use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidLocationHeaderException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\LocationHeaderEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\ConnectionProxyFactory;
use SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests\CreateStoreIntegrationHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests\DeleteStoreIntegrationHttpRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Drop-in replacement for integration-core's StoreIntegrationProxy that propagates
 * ConnectionData::merchantId into the transport channel Simba reads.
 *
 * Core sends merchantId only via the `Sequra-Merchant-Id` HTTP header (set by
 * AuthorizedProxy). Simba's StoreIntegrationsController reads `params[:merchant_id]`
 * from the request body / query string, not from headers, and the shared-account
 * impersonation path (`authorize_merchant_access`) refuses 403 when the param is
 * missing. Without this proxy, every POST/DELETE from a service-type api_account
 * fails authorization.
 *
 * The fix lives in middleware (not core) because the body/query placement is a
 * deployment-topology concern of the shared-account model that consumers of
 * sequra/middleware all share. integration-core stays untouched.
 *
 * For POST: merchant_id is injected as a top-level field in the JSON body.
 * For DELETE: merchant_id is injected as a query-string parameter on the URL.
 *
 * Behavior is a no-op when ConnectionData carries a null/empty merchantId.
 */
class MerchantIdAwareStoreIntegrationProxy implements StoreIntegrationsProxyInterface
{
    public function __construct(private ConnectionProxyFactory $connectionProxyFactory)
    {
    }

    /**
     * @throws DeploymentNotFoundException
     * @throws HttpRequestException
     * @throws InvalidLocationHeaderException
     * @throws LocationHeaderEmptyException
     */
    public function createStoreIntegration(CreateStoreIntegrationRequest $request): CreateStoreIntegrationResponse
    {
        $httpRequest = new CreateStoreIntegrationHttpRequest($request);
        $this->injectMerchantIdIntoBody($httpRequest, $request->getConnectionData()->getMerchantId());

        $response = $this->connectionProxyFactory
            ->buildAuthorizedProxy($request->getConnectionData())
            ->post($httpRequest);

        $headers = array_change_key_case($response->getHeaders());
        $location = $headers['location'] ?? '';

        return CreateStoreIntegrationResponse::fromLocationHeader($location);
    }

    /**
     * @throws DeploymentNotFoundException
     * @throws HttpRequestException
     */
    public function deleteStoreIntegration(DeleteStoreIntegrationRequest $request): DeleteStoreIntegrationResponse
    {
        $httpRequest = new DeleteStoreIntegrationHttpRequest($request);
        $this->injectMerchantIdIntoQuery($httpRequest, $request->getConnectionData()->getMerchantId());

        $this->connectionProxyFactory
            ->buildAuthorizedProxy($request->getConnectionData())
            ->delete($httpRequest);

        return new DeleteStoreIntegrationResponse();
    }

    private function injectMerchantIdIntoBody(CreateStoreIntegrationHttpRequest $httpRequest, ?string $merchantId): void
    {
        if ($merchantId === null || $merchantId === '') {
            return;
        }

        $body = $httpRequest->getBody();
        $body['merchant_id'] = $merchantId;
        $httpRequest->setBody($body);
    }

    private function injectMerchantIdIntoQuery(DeleteStoreIntegrationHttpRequest $httpRequest, ?string $merchantId): void
    {
        if ($merchantId === null || $merchantId === '') {
            return;
        }

        $queries = $httpRequest->getQueries();
        $queries['merchant_id'] = $merchantId;
        $httpRequest->setQueries($queries);
    }
}
