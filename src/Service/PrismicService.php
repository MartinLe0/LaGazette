<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for interacting with the Prismic CMS API.
 */
class PrismicService
{
    private string $apiEndpoint;
    private string $apiToken;

    /**
     * @param HttpClientInterface $httpClient The HTTP client
     * @param TagAwareCacheInterface $cache The cache interface
     * @param LoggerInterface $logger The logger interface
     * @param string $prismicRepoName The Prismic repository name (from parameters)
     * @param string $prismicToken The Prismic API token (from parameters)
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private TagAwareCacheInterface $cache,
        private LoggerInterface $logger,
        string $prismicRepoName,
        string $prismicToken
    ) {
        $this->apiEndpoint = sprintf('https://%s.prismic.io/api/v2', $prismicRepoName);
        $this->apiToken = $prismicToken;
    }

    /**
     * Get the master ref from Prismic API
     */
    private function getMasterRef(): string
    {
        return $this->cache->get('prismic_master_ref', function (ItemInterface $item) {
            $item->expiresAfter(60); // Check for new ref every minute

            $response = $this->httpClient->request('GET', $this->apiEndpoint, [
                'query' => ['access_token' => $this->apiToken]
            ]);

            $data = $response->toArray();
            foreach ($data['refs'] as $ref) {
                if ($ref['isMasterRef']) {
                    return $ref['ref'];
                }
            }

            throw new \RuntimeException('Master ref not found in Prismic API');
        });
    }

    /**
     * Fetch a document by its slug/type and filter its data
     */
    public function getDocument(string $type, string $slug): ?array
    {
        $cacheKey = sprintf('prismic_doc_%s_%s', $type, $slug);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($type, $slug) {
                $item->expiresAfter(3600); // 1 hour TTL
                $item->tag(['prismic_content']);

                $ref = $this->getMasterRef();
                $query = sprintf('[[at(my.%s.uid, "%s")]]', $type, $slug);

                $response = $this->httpClient->request('GET', $this->apiEndpoint . '/documents/search', [
                    'query' => [
                        'ref' => $ref,
                        'q' => $query,
                        'access_token' => $this->apiToken
                    ],
                    'timeout' => 5, // Timeout to prevent long waits
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException('Prismic API returned ' . $response->getStatusCode());
                }

                $results = $response->toArray()['results'] ?? [];
                if (empty($results)) {
                    return null;
                }

                return $this->filterData($results[0]);
            });
        } catch (\Exception $e) {
            $this->logger->error('Prismic Integration Error: ' . $e->getMessage(), [
                'type' => $type,
                'slug' => $slug
            ]);

            // In production, we could return a previously cached value even if expired
            // (requires a fallback cache or different cache strategy like stale-if-error)
            return null;
        }
    }

    /**
     * Filter sensitive or unnecessary metadata from Prismic response
     */
    private function filterData(array $document): array
    {
        // Keep only what's needed for the frontend
        return [
            'id' => $document['id'],
            'uid' => $document['uid'],
            'type' => $document['type'],
            'data' => $document['data'], // This contains the actual CMS content
            'last_publication_date' => $document['last_publication_date'],
        ];
    }

    /**
     * Clear Prismic cache
     */
    public function clearCache(): void
    {
        $this->cache->invalidateTags(['prismic_content']);
    }
}
